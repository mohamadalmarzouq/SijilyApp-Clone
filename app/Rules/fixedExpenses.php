<?php

namespace App\Rules;
use App\Models\Type;

use Illuminate\Contracts\Validation\Rule;

class fixedExpenses implements Rule
{
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
        $check = array(8,9,10,11);
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
        return 'Invalid Fixed Expense Id';
    }
}
