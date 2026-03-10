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

        // Lempar exception dengan status code 404
        if (!$user) {
            throw new Exception('User not found', 404);
        }

        // Lempar exception dengan status code 401
        if (!Hash::check($password, $user->password)) {
            throw new Exception('Invalid password', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }
}