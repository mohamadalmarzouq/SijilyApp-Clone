<?php   namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpVideo extends Model
{
    protected $table = 'help_videos';

    protected $fillable = ['title','title_ar', 'video', 'thumb_nail', 'embedded_url', 'url', 'type'];

     protected $module_name = 'help_videos';

    public function getModuleName(){
            return $this->module_name.".";
    }
    public function preventSearch(){
        return [
                'Action',
             ];
    }

    public function getColumnsForDataTable()
    {
        $data = [
            ['data' => 'title', 'name' => 'title', 'title' => 'Title'],
            // ['data' => 'thumb_nail', 'name' => 'thumb_nail', 'title' => 'Thumbnail'],
            // ['data' => 'video', 'name' => 'video', 'title' => 'Video'],
            ['data' => 'embedded_url', 'name' => 'embedded_url', 'title' => 'Embedded Url'],
            ['data' => 'url', 'name' => 'url', 'title' => 'Url'],

            // ['data' => 'type', 'name' => 'type', 'title' => 'Type'],
            ['data' => 'action', 'name' => 'Action', 'searchable' => 'false'],
        ];

        return json_encode($data);
    }

    public function orderArray()
    {
        return [
            ['data' => 'title', 'name' => 'title', 'order' => true,"search"=>true],
            // ['data' => 'thumb_nail', 'name' => 'thumb_nail', 'order' => true],
            // ['data' => 'video', 'name' => 'video', 'order' => true],
            ['data' => 'action', 'name' => 'Action', 'order' => false],
        ];
    }

    public function orderingColumn()
    {
        return json_encode([['1', 'desc']]);
    }
}
