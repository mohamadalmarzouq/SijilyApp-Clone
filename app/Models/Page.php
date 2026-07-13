<?php   namespace App\Models;

use GoogleCloudVision\Request\Request;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'pages';

    protected $fillable = ['title','title_ar', 'slug', 'description','description_ar'];

    protected $module_name = 'pages';

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
            ['data' => 'action', 'name' => 'Action', 'searchable' => 'false'],
        ];

        return json_encode($data);
    }

    public function orderArray()
    {
        return [
            ['data' => 'title', 'name' => 'title', 'order' => true,"search"=>true],
            ['data' => 'action', 'name' => 'Action', 'order' => false],
        ];
    }

    public function orderingColumn()
    {
        return json_encode([['1', 'desc']]);
    }

    public function page($id){
        $data =  $this->where('id',$id)->get()->first()->toArray();
        $description  = $data['description'];//strip_tags($data['description']);
        $description_ar  = $data['description_ar'];//strip_tags($data['description_ar']);
        $data['description'] = $description;
        $data['description_ar'] = $description_ar;
        return $data;
    }
}
