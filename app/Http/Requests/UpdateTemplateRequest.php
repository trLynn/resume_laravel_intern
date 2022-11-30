<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Validate Template Request
 *
 * @author Thu Ta
 * @create 28/06/2022
 */
class UpdateTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'template_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'layout_id' => 'required|integer',
            'login_id' => 'required|integer',
            'headings.*.heading_id' => 'integer',
            'headings.*.heading_name' => 'required|max:255',
            'headings.*.type_id' => 'required|integer|between:1,8',
            'headings.*.require_flag' => 'bail|required|integer|between:1,2',
            'headings.*.subheadings.*' => 'string|max:255',
            'headings.*.level.level_cat' => 'bail|integer|between:0,3',
        ];
    }

    public function messages()
    {
        return [
            'headings.*.heading_id.integer' => 'The heading_id field in one of the headings must be an integer.',
            'headings.*.heading_name.required' => 'The heading name field in one of the headings is required.',
            'headings.*.heading_name.max' => 'Headings may not be greater than 255 characters.',
            'headings.*.type_id.required' => 'The type_id field in one of the headings is required.',
            'headings.*.type_id.integer' => 'The type id field in one of the headings must be an integer.',
            'headings.*.type_id.between' => 'The type id field in one of the headings must be between 1 and 8.',
            'headings.*.require_flag.required' => 'The require_flag field in one of the headings is required.',
            'headings.*.require_flag.integer' => 'The require_flag field in one of the headings must be an integer.',
            'headings.*.require_flag.between' => 'The require_flag field in one of the headings must be between 1 and 2.',
            'headings.*.subheadings.*.max' => 'Subheadings may not be greater than 255 characters.',
            'headings.*.level.level_cat.integer' => 'The level category field in one of the headings must be an integer.',
            'headings.*.level.level_cat.between' => 'The level category field in one of the headings must be between 0 and 3.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['status'=>'NG', 'message'=>$validator->errors()],config('HTTP_CODE_422')));
    }
}
