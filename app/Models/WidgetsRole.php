<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetsRole extends Model
{
    protected $fillable = ['widget_id', 'role_id'];

    public function attachWidgetRoles($request, $id)
    {
        $this->where('widget_id', $id)->delete();
        if (isset($request->role_ids)) {
            foreach ($request->role_ids as $role) {
                $this->create([
                    'role_id' => $role,
                    'widget_id' => $id
                ]);
            }
        }
    }

    public function getWidgetRolesID($id)
    {

        $widgets = $this->where('widget_id', $id)->get(['role_id']);

        $widget_ids = [];

        foreach ($widgets as $widget) {
            $widget_ids[] = $widget->role_id;

        }

        return $widget_ids;
    }
}
