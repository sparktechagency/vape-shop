<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ManageProductRequest extends FormRequest
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
            'category_id' => 'required|exists:categories,id',
            'product_name' => 'required|string|max:255',
            'product_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'product_price' => 'required|numeric|min:0',
            'brand_name' => 'required|string|max:255',
            'product_discount' => 'nullable|numeric|min:0|max:100',
            'product_discount_unit' => 'nullable|numeric|min:0',
            'product_stock' => 'required|integer|min:0',
            'product_description' => 'required|string|max:1000',
            'product_faqs' => 'nullable|array',
            'product_faqs.*.question' => 'required|string|max:255',
            'product_faqs.*.answer' => 'required|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */

    // public function messages(): array
    // {
    //     return [
    //         'product_name.required' => 'Product name is required.',
    //         'product_image.required' => 'Product image is required.',
    //         'product_price.required' => 'Product price is required.',
    //         'brand_name.required' => 'Brand name is required.',
    //         'product_discount.numeric' => 'Product discount must be a number.',
    //         'product_discount.min' => 'Product discount must be at least 0.',
    //         'product_discount.max' => 'Product discount must not exceed 100.',
    //         'product_stock.required' => 'Product stock is required.',
    //         'product_description.required' => 'Product description is required.',
    //     ];
    // }
    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'Category ID',
            'product_name' => 'Product Name',
            'product_image' => 'Product Image',
            'product_price' => 'Product Price',
            'brand_name' => 'Brand Name',
            'product_discount' => 'Product Discount',
            'product_discount_unit' => 'Product Discount Unit',
            'product_stock' => 'Product Stock',
            'product_description' => 'Product Description',
            'product_faqs' => 'Product FAQs',
        ];
    }


    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->error($validator->errors()->first(), 422, $validator->errors())
        );
    }


}
