<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $fillable = [
        'name',
        'status'
    ];

// 'date_to',
//         'opening_balance',
//         'actual_balance',
//         'cash_in',
//         'cash_out',
//         'ending_balance',
//         'variance',

    protected $visible = ['id','name'];

    public function getColumnsForDataTable()
    {
        $data = [
            ['data' => 'name', 'name' => 'name', 'title' => 'Industry Name'],
            ['data' => 'action', 'name' => 'Action', 'searchable' => 'false'],
        ];

        return json_encode($data);
    }

    public function get_industry($id){

       $industry =  $this->where('id',$id)->first();
       return json_encode($industry);
    }

    public function orderArray()
    {
        return [
            ['data' => 'name', 'name' => 'name', 'order' => true],
            ['data' => 'action', 'name' => 'Action', 'order' => false],
        ];
    }

    public function orderingColumn()
    {
        return json_encode([['1', 'desc']]);
    }

    public function apiListing($limit)
    {
        $data=[];
        $result =  $this->orderBy('id','asc')->get()
            ->toArray();
            $fulldata['data']  = $result;
            $newData = [];
            foreach($fulldata['data'] as $full){
                $value = strtolower(str_replace(' ','_',str_replace('&','and',$full['name'])));
                $full['name_ar'] = trans('industries.'.$value);
                $newData[] = $full;
            }
            $fulldata['data'] = $newData;
           // $data['page'] =  $result;
            unset($data['page']['data']);
            unset($fulldata['data']['message']);
            $new_row = array_merge($fulldata,$data);
            return $new_row;
    }


}
