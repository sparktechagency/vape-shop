<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class TrendingAdProductRequest extends FormRequest
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
            // 'card_number' => 'required|string|min:13|max:16',
            // 'expiration_month' => 'required|numeric|between:1,12',
            // 'expiration_year' => 'required|numeric|digits:4|date_format:Y|after_or_equal:today',
            // 'cvc' => 'required|string|min:3|max:4',
            'category_id' => 'required|exists:categories,id',
            'region_id' => 'required|exists:regions,id',
            'product_id' => 'required|exists:manage_products,id',
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
            // 'card_number.required' => 'The card number is required.',
            // 'card_number.string' => 'The card number must be a string.',
            // 'card_number.min' => 'The card number must be at least 13 characters.',
            // 'card_number.max' => 'The card number may not be greater than 16 characters.',
            // 'expiration_month.required' => 'The expiration month is required.',
            // 'expiration_month.numeric' => 'The expiration month must be a number.',
            // 'expiration_month.between' => 'The expiration month must be between 1 and 12.',
            // 'expiration_year.required' => 'The expiration year is required.',
            // 'expiration_year.numeric' => 'The expiration year must be a number.',
            // 'expiration_year.digits' => 'The expiration year must be 4 digits.',
            // 'expiration_year.date_format' => 'The expiration year must be in the format YYYY.',
            // 'expiration_year.after_or_equal' => 'The expiration year must be today or later.',
            // 'cvc.required' => 'The CVC is required.',
            // 'cvc.string' => 'The CVC must be a string.',
            // 'cvc.min' => 'The CVC must be at least 3 characters.',
            // 'cvc.max' => 'The CVC may not be greater than 4 characters.',
            // 'amount.required' => 'The amount is required.',
            // 'amount.numeric' => 'The amount must be a number.',
            'category_id.required' => 'The category is required.',
            'category_id.exists' => 'The selected category does not exist.',
            'region_id.required' => 'The region is required.',
            'region_id.exists' => 'The selected region does not exist.',
            'product_id.required' => 'The product is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'slot.integer' => 'The slot must be an integer.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
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
