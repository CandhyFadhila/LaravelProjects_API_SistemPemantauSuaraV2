<?php

namespace App\Http\Requests\KPU;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportSuaraKPURequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kpu_file' => 'required|mimes:xls,csv,xlsx',
        ];
    }

    public function messages()
    {
        return [
            'kpu_file.required' => 'Silahkan masukkan file data KPU terlebih dahulu.',
            'kpu_file.mimes' => 'File data KPU wajib berupa excel dan berekstensi .xls, .csv., dan .xlsx.',
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
