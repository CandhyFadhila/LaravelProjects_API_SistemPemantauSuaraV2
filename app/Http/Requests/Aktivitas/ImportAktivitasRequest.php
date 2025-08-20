<?php

namespace App\Http\Requests\Aktivitas;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportAktivitasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'aktivitas_file' => 'required|mimes:xls,csv,xlsx',
        ];
    }

    public function messages()
    {
        return [
            'aktivitas_file.required' => 'Silahkan masukkan file data aktivitas terlebih dahulu.',
            'aktivitas_file.mimes' => 'File data aktivitas wajib berupa excel dan berekstensi .xls, .csv., dan .xlsx.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = [
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => $validator->errors()
        ];

        throw new HttpResponseException(response()->json($response, Response::HTTP_BAD_REQUEST));
    }
}
