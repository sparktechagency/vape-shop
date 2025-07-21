<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole\Role;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    //return all request data

public function rules(): array
{
    $rules = [
        'last_name' => 'nullable|string|max:255',
        'role' => 'required|string|max:255|in:2,3,4,5,6',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'phone' => 'nullable|regex:/^\+?[0-9\s\-\(\)]{10,20}$/|unique:users',
        'region_id' => $this->input('role') == Role::STORE->value ? 'required|string|max:255|exists:regions,id' : 'nullable|string|max:255| exists:regions,id',
        'address' => $this->input('role') == Role::STORE->value ? 'required|string|max:255' : 'nullable|string|max:255',
        'zip_code' => $this->input('role') == Role::STORE->value ? 'required|string|max:10' : 'nullable|string|max:10',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'ein' => 'nullable|string|max:20|unique:users,ein',
    ];

    if ($this->input('role') == Role::STORE->value) {
        $rules = array_merge(['store_name' => 'required|string|max:255'], $rules);
    } elseif ($this->input('role') == Role::BRAND->value) {
        $rules = array_merge(['brand_name' => 'required|string|max:255'], $rules);
    } else {
        $rules = array_merge(['first_name' => 'required|string|max:255'], $rules);
    }

    return $rules;
}

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'first_name.string' => 'First name must be a string',
            'first_name.max' => 'First name must not exceed 255 characters',
            'last_name.string' => 'Last name must be a string',
            'last_name.max' => 'Last name must not exceed 255 characters',
            'email.required' => 'Email is required',
            'email.string' => 'Email must be a string',
            'email.email' => 'Must be a valid email address',
            'email.max' => 'Email must not exceed 255 characters',
            'email.unique' => 'Email has already been taken',
            'role.in' => 'The selected role is invalid. Please choose a valid user role.',
            'password.required' => 'Password is required',
            'password.string' => 'Password must be a string',
            'password.min' => 'Password must be at least 8 characters long',
            'password.confirmed' => 'Password confirmation does not match',
            'phone.regex' => 'Phone number format is invalid',
            'phone.unique' => 'Phone number has already been taken',
            'address.string' => 'Address must be a string',
            'address.max' => 'Address must not exceed 255 characters',
            'zip_code.string' => 'Zip code must be a string',
            'zip_code.max' => 'Zip code must not exceed 10 characters',
            'region_id.required' => 'Region is required',
        ];
    }
    public function attributes(): array
    {
        return [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email Address',
            'password' => 'Password',
            'phone' => 'Phone Number',
            'address' => 'Address',
            'zip_code' => 'Zip Code',
            'region_id' => 'Region ID',
            'store_name' => 'Store Name',
            'brand_name' => 'Brand Name',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'ein' => 'Employer Identification Number (EIN)',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->error($validator->errors()->first(), 422, $validator->errors())
        );
    }
}
