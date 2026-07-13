<?php

namespace App;

use App\Models\Notification;
use App\Models\Property;
use App\Models\Role;
use App\Models\Status;
use App\Permissions\HasPermissionsTrait;
use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoin;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use PhpJunior\LaravelGlobalSearch\Traits\GlobalSearchable;

class User extends Authenticatable
{
    use Notifiable, GlobalSearchable, EloquentJoin, HasPermissionsTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'user_status_id', 'photo', 'address', 'contact', 'creator_id', 'notification_enable'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The columns that should be ordered.
     *
     * @var array
     */
    protected $order = [
        'name' => 'desc',
        'email' => 'desc'
    ];

    protected $appends = ['user_status'];

    public function getColumnsForDataTable()
    {
        $data = [
            ['data' => 'name', 'name' => 'name', 'title' => 'User Name'],
            ['data' => 'role.name', 'name' => 'role.name', 'title' => 'User Role'],
            ['data' => 'email', 'name' => 'email', 'title' => 'User Email ID'],
            ['data' => 'user_status', 'name' => 'user_status', 'searchable' => 'false'],
            ['data' => 'action', 'name' => 'Action', 'searchable' => 'false'],
            ['data' => 'created_at', 'name' => 'created_at', 'visible' => false]
        ];

        return json_encode($data);
    }

    public function orderArray()
    {
        return [
            ['data' => 'name', 'name' => 'name', 'order' => true],
            ['data' => 'role.name', 'name' => 'role.name', 'order' => true, 'relationship' => ['model' => 'payment_method', 'column_name' => 'name']],
            ['data' => 'email', 'name' => 'email', 'order' => true],
            ['data' => 'properties_assigned', 'name' => 'properties_assigned', 'order' => false],
            ['data' => 'user_status_id', 'name' => 'user_status_id', 'order' => true],
            ['data' => 'action', 'name' => 'Action', 'order' => false],
            ['name' => 'created_at', 'order' => false]
        ];
    }

    public function orderingColumn()
    {
        return json_encode([['6', 'desc']]);
    }

    public function getUserStatusAttribute()
    {
        $status = $this->status->slug;

        $status_name = $this->status->status;

        return View::make('panel.includes.status_mutator', compact('status', 'status_name'))->render();
    }


    public function status()
    {
        return $this->belongsTo(Status::class, 'user_status_id')->where('module', $this->getTable());
    }

    public function sendMail($user, $body)
    {
        Mail::raw($body, function ($message) use ($user) {

            $message->to($user->email)->subject('Rent Portal Verification');

        });
    }

    public function ajaxListing()
    {
        $current_user = Auth()->user();

        $query = $this->with(['role']);

        if (!in_array($current_user->role_id, $this->getSuperAdminRoleIds())) {

            $query = $query->where('creator_id', $current_user->id);
        }

        return $query;
    }

    public function getSuperAdminRoleIds()
    {
        return [1];
    }


    public function checkIfUserStatusChanged($user, $status)
    {
        $status_model = new Status();

        $status_id = $status_model->getStatusID($this->getTable(), 'active');

        return $user->user_status_id != $status && $status_id == $status && in_array($user->role_id, $this->getLandLordRoleIds());
    }

}
