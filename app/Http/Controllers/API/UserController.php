<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function updateProfile(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Anda harus login untuk memperbarui profil!',
            ], 401);
        }

        $user = Auth::user();

        // validasi dinamis (semua opsional, tapi kalau ada password harus sesuai rule)
        $rules = [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string'], // password lama
            'new_password' => ['nullable', 'string', 'min:6', 'confirmed'], // confirmed = otomatis cek dengan field new_password_confirmation
        ];

        $validated = $request->validate($rules);

        // Update name / email kalau dikirim
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        if ($request->filled('email')) {
            $user->email = $request->email;
        }

        // Update password kalau semua field ada
        if ($request->filled('password') && $request->filled('new_password')) {
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Password lama salah!'
                ], 422);
            }

            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => $user
        ]);
    }
}
