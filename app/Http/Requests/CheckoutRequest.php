<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:15',
            'customer_address' => 'nullable|string|max:500',
            'customer_dob' => 'nullable|date_format:d-m-Y',
            'cart_items' => 'required|array|min:1',
            'cart_items.*.product_id' => 'required|integer|exists:store_products,id',
            'cart_items.*.quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'grand_total.required' => 'Grand total is required.',
            'grand_total.numeric' => 'Grand total must be a number.',
            'grand_total.min' => 'Grand total must be at least 0.',
            'customer_name.string' => 'Customer name must be a string.',
            'customer_name.max' => 'Customer name may not be greater than 255 characters.',
            'customer_email.email' => 'Customer email must be a valid email address.',
            'customer_email.max' => 'Customer email may not be greater than 255 characters.',
            'customer_phone.string' => 'Customer phone must be a string.',
            'customer_phone.max' => 'Customer phone may not be greater than 15 characters.',
            'customer_address.required' => 'Customer address is required.',
            'customer_address.string' => 'Customer address must be a string.',
            'customer_address.max' => 'Customer address may not be greater than 500 characters.',
            'customer_dob.date_format' => 'Customer date of birth must be in the format Y-m-d.',
            'cart_items.required' => 'Cart items are required.',
            'cart_items.array' => 'Cart items must be an array.',
            'cart_items.min' => 'At least one cart item is required.',
            'cart_items.*.product_id.required' => 'Product ID is required for each cart item.',
            'cart_items.*.product_id.integer' => 'Product ID must be an integer.',
            'cart_items.*.product_id.exists' => 'The selected product does not exist.',
            'cart_items.*.quantity.required' => 'Quantity is required for each cart item.',
            'cart_items.*.quantity.integer' => 'Quantity must be an integer.',
            'cart_items.*.quantity.min' => 'Quantity must be at least 1.',
        ];

    }

     protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->error($validator->errors()->first(), 422, $validator->errors())
        );
    }
}



