<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\SubCategory;

class CustomValidation implements Rule
{
    public $_user_id= NULL;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($user_id)
    {
        $this->_user_id = $user_id;
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
        $SubCategory = new SubCategory();
      //define rule
      $category = $SubCategory->whereNull('deleted_at')
         ->where('user_id',$this->_user_id)
            ->where('title', '=',$value)
         ->exists();
      return $category ? 0 : 1;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The title has already been taken.';
    }
}
