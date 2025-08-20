<?php

namespace App\Http\Controllers\Publik\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Login\ResetPasswordRequest;
use App\Http\Resources\public\WithoutDataResource;

class ResetPasswordController extends Controller
{
    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        $user = User::where('username', $data['username'])->first();
        if (!$user) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengguna dengan username tersebut tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Setel ulang kata sandi pengguna
        $user->password = Hash::make($data['password']);
        $user->save();

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Kata sandi baru anda berhasil diubah.'), Response::HTTP_OK);
    }
}
