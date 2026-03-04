<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validateRequest = Validator::make($request->all(), [
            "email" => "required",
            "password" => "required",
        ]);

        if ($validateRequest->fails()) {
            return $this->sendError("Validation Error", $validateRequest->errors(), 422);
        }

        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->sendError("User not found", [], 404);
            }

            if (!Hash::check($request->password, $user->password)) {
                return $this->sendError("Invalid password", [], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->sendResponse([
                'token' => $token,
                'user' => $user,
            ], 'User logged in successfully');
        } catch (\Exception $e) {
            $message = [
                "line" => $e->getLine(),
                "file" => $e->getFile(),
                "message" => $e->getMessage(),
            ];
            Log::critical($message);
            return $this->sendError($e->getMessage(), $message, 500);
        }
    }
}
