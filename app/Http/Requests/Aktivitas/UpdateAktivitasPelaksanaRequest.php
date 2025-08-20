<?php

namespace App\Http\Requests\Aktivitas;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAktivitasPelaksanaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'nama_aktivitas' => 'required|string',
            // 'kelurahan_id' => 'required|integer|exists:kelurahans,id',
            // 'rw' => 'required|integer',
            // 'pelaksana_id' => 'required|integer|exists:users,id',
            'deskripsi' => 'nullable|string',
            'tgl_mulai' => 'required|string',
            'tgl_selesai' => 'required|string',
            'tempat_aktivitas' => 'required|string',
            'potensi_suara' => 'required|integer',
            'foto_aktivitas' => $this->hasFile('foto_aktivitas')
                ? 'required|image|mimes:jpeg,png,jpg|max:10240'  // Validasi untuk file
                : 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_aktivitas.required' => 'Nama aktivitas tidak diperbolehkan kosong.',
            'nama_aktivitas.string' => 'Nama aktivitas tidak diperbolehkan mengandung selain huruf dan angka.',
            'kelurahan_id.required' => 'Silahkan pilih kelurahan terlebih dahulu.',
            'kelurahan_id.integer' => 'Kelurahan tidak diperbolehkan mengandung selain angka.',
            'kelurahan_id.exists' => 'Kelurahan tersebut tidak ada dalam database.',
            'rw.required' => 'Lokasi RW tidak diperbolehkan kosong.',
            'rw.integer' => 'Lokasi RW tidak diperbolehkan mengandung selain angka.',
            'pelaksana_id.required' => 'Nama pelaksana tidak diperbolehkan kosong.',
            'pelaksana_id.integer' => 'Nama pelaksana tidak diperbolehkan mengandung selain angka.',
            'pelaksana_id.exists' => 'Nama pelaksana tersebut tidak ada dalam database.',
            'deskripsi.string' => 'Deksripsi aktivitas tidak diperbolehkan mengandung selain huruf.',
            'tgl_mulai.required' => 'Tanggal mulai aktivitas tidak diperbolehkan kosong.',
            'tgl_mulai.string' => 'Tanggal mulai aktivitas tidak diperbolehkan mengandung selain huruf.',
            'tgl_selesai.required' => 'Tanggal selesai aktivitas tidak diperbolehkan kosong.',
            'tgl_selesai.string' => 'Tanggal selesai aktivitas tidak diperbolehkan mengandung selain huruf.',
            'tempat_aktivitas.required' => 'Latar aktivitas tidak diperbolehkan kosong.',
            'tempat_aktivitas.string' => 'Latar aktivitas tidak diperbolehkan mengandung selain huruf dan angka.',
            'foto_aktivitas.required' => 'Bukti foto aktivitas tidak diperbolehkan kosong.',
            'foto_aktivitas.image' => 'Foto aktivitas harus berupa gambar.',
            'foto_aktivitas.mimes' => 'Tipe file foto yang diperbolehkan hanya jpg, jpeg, dan png.',
            'foto_aktivitas.max' => 'Ukuran file foto yang diperbolehkan maksimal 10 MB.',
            'potensi_suara.required' => 'Potensi suara yang didapat tidak diperbolehkan kosong.',
            'potensi_suara.integer' => 'Potensi suara yang didapat tidak diperbolehkan mengandung selain angka.',
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
