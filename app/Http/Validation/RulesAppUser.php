<?php

namespace App\Http\Validation;

use Illuminate\Http\Request;
use App\Rules\CustomValidation;

class RulesAppUser
{

    /**
     * Get the validation rules that apply to the requests For AppUserController.
     *
     * @return array
     */
    public static function userSignUp()
    {
        return [
            'full_name' => 'required|max:20',
            // 'user_name' => 'required|unique:app_users,user_name',
            'email' => 'required|email|max:99|unique:app_users,email,NULL,id,deleted,0',
            'password' => 'required|min:8',
            'business_name' => 'required|unique:app_users,business_name|max:99',
            'industry_type' => 'required',
            'industry_name' => 'required',
            'address' => 'required|max:60',
            'city' => 'required',
            'country' => 'required',
            'postal_code'=>'max:10',
            'country_name' => 'required',
            'company_year_end_date' => 'required',
            'country_code' => 'required',
            'phone' => 'required',
        ];
    }


    public static function panelUserSignUp()
    {
        return [
            'full_name' => 'required|max:20',
            'user_name' => 'required|max:16',
            'email' => 'required|email|max:99|unique:app_users,email,NULL,id,deleted,0',
            'password' => 'required|min:8',
            'business_name' => 'required|unique:app_users,business_name|max:50',
            'industry_type' => 'required',
            'industry_name' => 'required',
            'address' => 'required|max:60',
            'city' => 'required|max:30',
            'country' => 'required',
            'language' => 'required',
            'country_name' => 'required',
            'company_year_end_date' => 'required',
            'postal_code'=>'max:10',
            'contact'=>'regex:/^([0-9\s\-\+\(\)]*)$/|min:8|max:8'
        ];
    }
//  'email' => 'email|max:99|unique:app_users,email,' . $request->user_id,
    public static function panelEditUserSignUp($request)
    {
        return [
            'full_name' => 'required|max:20',
            'user_name' => 'required|max:16',
            'business_name' => 'required|max:50|unique:app_users,business_name,' .$request->id,
            'industry_type' => 'required',
            'industry_name' => 'required',
            'address' => 'required|max:60',
            'city' => 'required|max:30',
            'country' => 'required',
            'language' => 'required',
            'country_name' => 'required',
            'company_year_end_date' => 'required',
            'postal_code'=>'max:10',
            'contact'=>'regex:/^([0-9\s\-\+\(\)]*)$/|min:8|max:8'
        ];
    }


    public static function verifyCode()
    {
        return [
            'code' => 'required',
        ];
    }

    public static function userLogin()
    {
        return [
            'email' => 'required|exists:app_users,email',
            'password' => 'required|min:8'
        ];
    }

    public static function forgotPassword()
    {
        return [
            'email' => 'required|email|exists:app_users,email,is_verified,1',
        ];
    }

    public static function resetPassword()
    {
        return [
            'password' => 'required|min:8',
        ];
    }

    public static function changeLanguage($languages)
    {
        return [
            'language' => 'required|in:' . $languages,
        ];
    }

    public static function updateProfile($request)
    {
        return [
            // 'full_name' => 'required',
            'email' => 'email|max:99|unique:app_users,email,' . $request->user_id,
            // 'contact' => 'required|regex:/\+\d{0,3}.\-[0-9]{7,}/|unique:app_users,contact,' . $request->user_id,
            // 'business_name' => 'max:99|unique:app_users,business_name,' . $request->user_id,
        ];
    }

    public static function changePassword()
    {
        return [
            'password' => 'required',
            'old_password' => 'required'
        ];
    }

    public static function CheckEmail(){
        return [
            'email' => 'required|email',
        ];
    }

    public static function CheckBusiness(){
        return [
            'business_name' => 'required',
        ];
    }

    public static function Resend(){
        return [
            'email' => 'required|email|exists:app_users,email',
        ];
    }

    public static function Logout(){
        return [
            'email' => 'required|exists:app_users,id',
        ];
    }

    public static function storeSubCategory($id){
        return [
            'title' => new CustomValidation($id),//'required|unique:sub_categories,title,user_id'.$id,
//            'module' => 'required',
            'title' => 'required'
        ];
    }

    public static function deleteSubCategory(){
        return [
            'id' => 'required|exists:sub_categories,id'
        ];
    }

    public static function createUser()
    {
        return [
            'full_name' => 'required',
            'email' => 'required|email|max:150',
        ];
    }

    public static function createChildUser()
    {
        return [
            'full_name' => 'required',
            'email' => 'required|email|max:150',
            'role_id' => 'required',
            'password' => 'required',
        ];
    }
}

