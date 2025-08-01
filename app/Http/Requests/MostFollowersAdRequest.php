<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MostFollowersAdRequest extends FormRequest
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
            'region_id' => 'required|exists:regions,id',
            'amount' => 'required|numeric',
            'slot' => 'required|numeric|min:1|max:6',
            'preferred_duration' => 'required|in:1_week,2_weeks,1_month,3_months,6_months',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'region_id.required' => 'The region is required.',
            'region_id.exists' => 'The selected region does not exist.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'slot.required' => 'The slot is required.',
            'slot.numeric' => 'The slot must be a number.',
            'slot.min' => 'The slot must be at least 1.',
            'slot.max' => 'The slot must not exceed 6.',
            'preferred_duration.required' => 'The preferred duration is required.',
            'preferred_duration.in' => 'The preferred duration must be one of the following: 1_week, 2_weeks, 1_month, 3_months, 6_months.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->error($validator->errors()->first(), 422, $validator->errors())
        );
    }

}
