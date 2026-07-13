<?php   namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faqs';

    protected $fillable = ['question','question_ar', 'answer','answer_ar'];
    protected $module_name = 'faqs';

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
            ['data' => 'question', 'name' => 'question', 'title' => 'Question'],
            ['data' => 'answer', 'name' => 'answer', 'title' => 'Answer'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'],
            ['data' => 'action', 'name' => 'Action', 'searchable' => 'false'],
        ];
        return json_encode($data);
    }

    public function orderArray()
    {
        return [
            ['data' => 'question', 'name' => 'question', 'order' => true,"search"=>true],
            ['data' => 'answer', 'name' => 'answer', 'order' => true,"search"=>true],
            ['data' => 'created_at', 'name' => 'created_at', 'order' => false],
            ['data' => 'action', 'name' => 'Action', 'order' => false],
        ];
    }

    public function orderingColumn()
    {
        return json_encode([['2', 'desc']]);
    }

    public function faqs(){
        return $this->get()->toArray();
    }
}
