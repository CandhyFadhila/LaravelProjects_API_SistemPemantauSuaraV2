<?php

namespace App\Http\Requests\Pengguna;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePenggunaRequest extends FormRequest
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
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:0,1',
            'nik_ktp' => 'required|integer|digits:16|unique:users,nik_ktp',
            'tgl_diangkat' => 'required',
            'no_hp' => 'nullable|max:50',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:5048',
            'role_id' => 'required|integer',
            'kelurahan_id' => 'nullable',
            'rw_pelaksana' => 'nullable',
            'password' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama pengguna tidak diperbolehkan kosong.',
            'nama.string' => 'Nama pengguna tidak diperbolehkan mengandung selain huruf.',
            'nama.max' => 'Nama pengguna tidak diperbolehkan melebihi 255 baris kata.',
            'jenis_kelamin.required' => 'Jenis kelamin pengguna tidak diperbolehkan kosong.',
            'jenis_kelamin.in' => 'Jenis kelamin karyawan tidak diperbolehkan selain laki-laki atau perempuan.',
            'nik_ktp.required' => 'NIK KTP pengguna tidak diperbolehkan kosong.',
            'nik_ktp.integer' => 'NIK KTP pengguna tidak diperbolehkan mengandung selain angka.',
            'nik_ktp.digits' => 'NIK KTP pengguna melebihi batas maksimum panjang 16 karakter.',
            'nik_ktp.unique' => 'NIK KTP yang diberikan sudah pernah terdaftar di sistem Pemantau Suara.',
            'tgl_diangkat.required' => 'Tanggal masuk pengguna tidak diperbolehkan kosong.',
            'no_hp.max' => 'Nomor telepon pengguna tidak diperbolehkan melebihi 255 baris angka.',
            'foto_profil.image' => 'Foto profil pengguna harus berupa gambar.',
            'foto_profil.mimes' => 'Tipe file foto yang diperbolehkan hanya jpg, jpeg, dan png.',
            'foto_profil.max' => 'Ukuran file foto yang diperbolehkan maksimal 5 MB.',
            'role_id.required' => 'Role pengguna tidak diperbolehkan kosong.',
            'role_id.integer' => 'Role pengguna tidak diperbolehkan mengandung selain angka.',
            'password.required' => 'Password pengguna tidak diperbolehkan kosong.',
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
