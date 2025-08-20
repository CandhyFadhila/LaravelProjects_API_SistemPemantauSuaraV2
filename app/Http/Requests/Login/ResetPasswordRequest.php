<?php

namespace App\Http\Requests\Login;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|exists:users,username',
            'kode_otp' => 'required|numeric|digits:6',
            'password' => 'required|min:4|confirmed',
            'password_confirmation' => 'required|min:4',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'Email tidak diperbolehkan kosong.',
            'email.exists' => 'Email yang diberikan tidak terdaftar di sistem Pemantau Suara.',
            'password.required' => 'Kata sandi tidak diperbolehkan kosong.',
            'password.min' => 'Kata sandi minimal terdiri dari 4 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi kata sandi tidak diperbolehkan kosong.',
            'password_confirmation.min' => 'Konfirmasi kata sandi minimal terdiri dari 4 karakter.',
            'kode_otp.required' => 'OTP tidak diperbolehkan kosong.',
            'kode_otp.numeric' => 'OTP yang valid hanya diperbolehkan mengandung angka.',
            'kode_otp.digits' => 'Kode OTP harus terdiri dari 6 digit.'
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
