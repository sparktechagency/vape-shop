<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole\Role;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
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
    public function rules(): array
    {

        $rules = [
            'last_name' => 'nullable|string|max:255',
            // 'role' => 'required|string|max:255|in:2,3,4,5,6',
            // 'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|regex:/^\+?[0-9]{10,15}$/|unique:users,phone,'. Auth::id(),
            'address' => Auth::user()->role == Role::STORE->value ? 'required|string|max:255' : 'nullable|string|max:255',
            'zip_code' => Auth::user()->role == Role::STORE->value ? 'required|string|max:10' : 'nullable|string|max:10',
            'region_id' => Auth::user()->role == Role::STORE->value ? 'required|string|max:255' : 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ];

        if (Auth::user()->role == Role::STORE->value) {
            $rules = array_merge(['store_name' => 'required|string|max:255'], $rules);
        } elseif (Auth::user()->role == Role::BRAND->value) {
            $rules = array_merge(['brand_name' => 'required|string|max:255'], $rules);
        } else {
            $rules = array_merge(['first_name' => 'required|string|max:255'], $rules);
        }

        return $rules;
    }

    public function attributes() : array
    {
        return [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email',
            'phone' => 'Phone',
            'address' => 'Address',
            'zip_code' => 'Zip code',
            'region' => 'Region',
            'store_name' => 'Store name',
            'brand_name' => 'Brand name',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->error($validator->errors()->first(), 422, $validator->errors())
        );
    }
}
