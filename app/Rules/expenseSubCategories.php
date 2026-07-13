<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Type;

class expenseSubCategories implements Rule
{
    public $_id= NULL;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->_id = $id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $ctype='';
        $check = array(6,7);
        if(in_array($this->_id,$check) && in_array($value,$check)){
            $ctype = 1;
        }else{
            $ctype =0;
        }
        // $type = new Type();
        // //define rule
        // $ctype = $type->whereNull('deleted_at')
        //       ->whereIn('id',array(6,7))
        //    ->exists();
        return $ctype;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid Sub Category Id';
    }
}
