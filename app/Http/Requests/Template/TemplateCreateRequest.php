<?php

namespace App\Http\Requests\Template;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TemplateCreateRequest extends FormRequest
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
            'title'                         =>      'required|string',
            'layout_id'                     =>      'required|integer',
            'login_id'                      =>      'required|integer',
            'headings.*.heading_name'       =>      'required|string|max:255',
            'headings.*.type_id'            =>      'required|integer',
            'headings.*.subheadings.*'      =>      'string|max:255',
            'headings.*.level.level_cat'    =>      'bail|integer|between:0,3',
        ];
    }

    /**
     * Return when validation failed
     *
     * @author  Thu Ta
     * @create  2022/06/08
     * @return  response array
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['status' => 'NG', 'message' => $validator->errors()->all()], 422));
    }

    /**
     * Reply the custom error message.
     *
     * @author Thu Ta
     * @create  08/06/2022
     * @return array
     */
    public function messages()
    {
        return[

            'title.required'                            =>   'The title filed is required',
            'title.string'                              =>   'The title filed must be a string type',
            'layout_id.required'                        =>   'The layout filed is required',
            'layout_id.integer'                         =>   'The layout filed must be a integer type',
            'login_id.required'                         =>   'The layout filed is required',
            'login_id.integer'                          =>   'The layout filed must be a integer type',
            'headings.*.heading_name.required'          =>   'The heading name filed is required',
            'headings.*.heading_name.string'            =>   'The heading name filed must be a string type',
            'headings.*.heading_name.max'               =>   'The heading name may not be greater than 255 characters.',
            'headings.*.type_id.required'               =>   'The type filed is required',
            'headings.*.type_id.integer'                =>   'The type filed must be a integer type',
            'headings.*.subheadings.*.max'              =>   'The subheading name may not be greater than 255 characters.',
            'headings.*.level.level_cat.integer'        =>   'The level category field in one of the headings must be an integer.',
            'headings.*.level.level_cat.between'        =>   'The level category field in one of the headings must be between 0 and 3.',

        ];
    }
}
