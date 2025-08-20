<?php

namespace App\Http\Requests\Login;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyOTPRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'kode_otp' => 'required|numeric|digits:6'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email tidak diperbolehkan kosong.',
            'email.email' => 'Email yang valid hanya diperbolehkan menggunakan format email.',
            'kode_otp.required' => 'OTP tidak diperbolehkan kosong.',
            'kode_otp.numeric' => 'OTP yang valid hanya diperbolehkan mengandung angka.',
            'kode_otp.digits' => 'Kode OTP harus terdiri dari 6 digit.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $messages = implode(' ', $validator->errors()->all());
        $response = [
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => $messages,
        ];

        throw new HttpResponseException(response()->json($response, Response::HTTP_BAD_REQUEST));
    }
}
