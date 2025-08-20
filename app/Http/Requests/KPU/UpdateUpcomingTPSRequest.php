<?php

namespace App\Http\Requests\KPU;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUpcomingTPSRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kelurahan_id' => 'required|integer|exists:kelurahans,id',
            'tahun' => 'required|integer',
            'jumlah_tps' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'kelurahan_id.required' => 'Kolom kelurahan tidak boleh kosong.',
            'kelurahan_id.integer' => 'Kolom kelurahan hanya boleh berisi angka.',
            'kelurahan_id.exists' => 'Kelurahan yang dipilih tidak ditemukan di database.',
            'tahun.required' => 'Kolom tahun tidak boleh kosong.',
            'tahun.integer' => 'Kolom tahun hanya boleh berisi angka.',
            'jumlah_tps.required' => 'Kolom jumlah TPS tidak boleh kosong.',
            'jumlah_tps.integer' => 'Kolom jumlah TPS hanya boleh berisi angka.'
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
