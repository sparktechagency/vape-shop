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
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'cover_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'dob' => 'nullable|date_format:d-m-Y',
            // 'role' => 'required|string|max:255|in:2,3,4,5,6',
            // 'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|regex:/^\+?[0-9]{10,15}$/',
            'address' => Auth::user()->role == Role::STORE->value ? 'sometimes|string|max:255' : 'nullable|string|max:255',
            'zip_code' => Auth::user()->role == Role::STORE->value ? 'sometimes|string|max:10' : 'nullable|string|max:10',
            'region_id' => Auth::user()->role == Role::STORE->value ? 'sometimes|string|max:255' : 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'open_from' => 'nullable|date_format:H:i',
            'close_at' => 'nullable|date_format:H:i|',
            'ein' => 'nullable|string|max:20|unique:users,ein,'. Auth::id(),
            'pl' => 'nullable|boolean',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'shipping_cost' => 'nullable|numeric|min:0|max:9999.99',
        ];

        if (Auth::user()->role == Role::STORE->value) {
            $rules = array_merge(['store_name' => 'sometimes|string|max:255'], $rules);
        } elseif (Auth::user()->role == Role::BRAND->value) {
            $rules = array_merge(['brand_name' => 'sometimes|string|max:255'], $rules);
        } else {
            $rules = array_merge(['first_name' => 'sometimes|string|max:255'], $rules);
        }

        return $rules;
    }

    //message for validation for open and close time
    public function messages(): array
    {
        return [
            'open_from.date_format' => 'The open from time must be in the format HH:mm.',
            'close_at.date_format' => 'The close at time must be in the format HH:mm.',
        ];
    }

    public function attributes() : array
    {
        return [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'avatar' => 'Avatar',
            'cover_photo' => 'Cover photo',
            'email' => 'Email',
            'phone' => 'Phone',
            'address' => 'Address',
            'zip_code' => 'Zip code',
            'region' => 'Region',
            'store_name' => 'Store name',
            'brand_name' => 'Brand name',
            'open_from' => 'Open From',
            'close_at' => 'Close at',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'ein' => 'EIN',
            'pl' => 'PL',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->error($validator->errors()->first(), 422, $validator->errors())
        );
    }
}
