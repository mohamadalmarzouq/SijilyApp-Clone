<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'role_id' => 'required|exists:roles,id',
            'email' => 'required|email|unique:users,email',
            'user_status_id' => 'required|exists:statuses,id',
        ];
    }

    public function prepareForValidation()
    {
        $current_user = Auth()->user();

        $this->merge(['creator_id' => $current_user->id]);
    }
}
