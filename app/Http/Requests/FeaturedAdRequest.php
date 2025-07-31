<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeaturedAdRequest extends FormRequest
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
            'featured_article_id' => [
                'required',
                Rule::exists('posts', 'id')->where(function ($query) {
                    return $query->where('content_type', 'article');
                }),
            ],
            'region_id' => 'required|exists:regions,id',
            'preferred_duration' => 'required|in:1_week,2_weeks,1_month,3_months,6_months', // Duration options: 1 week, 2 weeks, 1 month, 3 months, 6 months
            'amount' => 'required|numeric',
            'slot' => 'nullable|integer|min:1|max:6', // Assuming slot is an integer and can be between 1 and 6
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
            'featured_article_id.required' => 'The featured article is required.',
            'featured_article_id.exists' => 'The selected featured article does not exist.',
            'region_id.required' => 'The region is required.',
            'region_id.exists' => 'The selected region does not exist.',
            'preferred_duration.required' => 'The preferred duration is required.',
            'preferred_duration.in' => 'The preferred duration must be one of the following: 1_week, 2_weeks, 1_month, 3_months, 6_months.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'slot.integer' => 'The slot must be an integer.',
            'slot.min' => 'The slot must be at least 1.',
            'slot.max' => 'The slot must not exceed 6.',

        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->error($validator->errors()->first(), 422, $validator->errors())
        );
    }
}
