<?php

namespace App\Http\Requests\Applicant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApplicantOtpRequest extends FormRequest
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
            'passcode' => 'required|digits:6',
        ];
    }

     /**
     * Set Validation Custom message.
     *
     * @return array
     */
    public function messages(){
        return [
            'passcode.digits' => 'The :attribute length must be 6.',
        ];
    }

    /**
     * Show Validation Error message.
     *
     * @return array
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => "NG",
            'message' => $validator->errors()
          ], 422));
    }
}
