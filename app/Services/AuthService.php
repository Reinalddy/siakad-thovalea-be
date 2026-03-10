<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Exception;

class AuthService
{
    /**
     * @throws Exception
     */
    public function login(string $email, string $password)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new Exception('User not found', 404);
        }

        if (!Hash::check($password, $user->password)) {
            throw new Exception('Invalid password', 401);
        }

        if (!$user->is_active) {
            throw new Exception('Akun Anda sedang dinonaktifkan. Hubungi BAAK.', 403);
        }

        // Buat Token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // Ambil role pertama dari user ini (Spatie)
        $role = $user->getRoleNames()->first(); 

        return [
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $role
            ],
        ];
    }
}