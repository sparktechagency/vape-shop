<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePasswordRequest extends FormRequest
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
        return [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ];
    }


    public function messages(): array
    {
        return [
            'current_password.required' => 'Please enter your Curren password',
            'current_password.string' => 'Current password must be a string',
            'current_password.min' => 'Current password must be at least 8 characters',
            'new_password.required' => 'Please enter your new password',
            'new_password.string' => 'New password must be a string',
            'new_password.min' => 'New password must be at least 8 characters',
            'new_password.confirmed' => 'New password and confirmation do not match'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->errorResponse($validator->errors()->first(), 422, $validator->errors())
        );
    }
}
