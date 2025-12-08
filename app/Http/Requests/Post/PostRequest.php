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
        $contentType = $this->input('content_type', 'post');
        $rules = [
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'content_type' => 'required|in:post,article',
        ];

        if ($contentType === 'article') {

            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        } else {
            $rules['images'] = 'nullable|array|min:1';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            $rules['is_in_gallery'] = 'sometimes|boolean';
        }

        return $rules;
    }

    //attributes
    public function attributes()
    {
        return [
            'title' => 'Title',
            'content' => 'Content',
            'article_image' => 'Image',
        ];
    }

    //response error
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->error($validator->errors()->first(), 422, $validator->errors())
        );
    }
}
