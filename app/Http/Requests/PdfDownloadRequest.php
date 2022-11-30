<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PdfDownloadRequest extends FormRequest
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
            'applicant_email'=>'required|email',
            'template_id'=>'required|integer'
        ];
    }
    /**
     * fail vaidation
     * @author Thu Rein Lynn
     * @created_at 28/6/2022
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['status' => 'NG', 'message' => $validator->errors()->all()], config('HTTP_CODE_422')));
    }
}
