<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApplicantInfoUpdateRequest extends FormRequest
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
            'template_name'=>'required|string',
            'checked'=>'required|between:0,1',
            'applicant_id' => 'required|integer',
            'template_id' => 'required|integer',
            'fields' => 'required|array',
            'fields.*.heading_id' => 'required|integer',
            'fields.*.heading_name' => 'nullable|string',
            'fields.*.value' => 'nullable',
            'fields.*.type_id' => 'required|integer',
            'fields.*.level_id' => 'nullable|integer',
            'fields.*.level_name' => 'nullable|string',
            'fields.*.required_flag' => 'required|integer|between:1,2',
            'fields.*.applicant_info_id' => 'nullable|integer',
            'fields.*.applicant_link_id' => 'nullable|integer',
            'fields.*.applicant_links' => 'nullable|array',
        ];
    }    /**
     * fail vaidation
     * @author Thu Ta
     * @created_at 28/6/2022
     */

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['status' => 'NG', 'message' => $validator->errors()->all()], config('HTTP_CODE_422')));
    }
}
