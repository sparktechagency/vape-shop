<?php

namespace App\Http\Requests\Post;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostRequest extends FormRequest
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
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
        ];
    }

    //attributes
    public function attributes()
    {
        return [
            'title' => 'Title',
            'content' => 'Content',
        ];
    }

    //response error
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->errorResponse($validator->errors()->first(), 422, $validator->errors())
        );
    }

}
