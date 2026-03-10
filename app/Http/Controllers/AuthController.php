<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    // Inject AuthService ke Controller
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $validateRequest = Validator::make($request->all(), [
            "email"    => "required|email",
            "password" => "required",
        ]);

        if ($validateRequest->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validateRequest->errors()->first(),
                'data'    => $validateRequest->errors(),
            ], 422);
        }

        try {
            $result = $this->authService->login($request->email, $request->password);

            return response()->json([
                'status'  => 'success',
                'message' => 'User logged in successfully',
                'data'    => $result,
            ], 200);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;

            $errorData = [
                "line"    => $e->getLine(),
                "file"    => $e->getFile(),
                "message" => $e->getMessage(),
            ];
            Log::critical('Login System Error:', $errorData);
                
            return response()->json([
                'status'  => 'error',
                'message' => 'Internal Server Error',
                'data'    => $errorData,
            ], $statusCode);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->getRoleNames()->first()
                ]
            ]);
        } catch (\Exception $e) {
            $message = [
                "line" => $e->getLine(),
                "file"=> $e->getFile(),
                "message"=> $e->getMessage(),
            ];

            Log::critical($message);

            return response()->json([
                "status"=> "error",
                "message"=> "Something went wrong",
                "data" => $message
            ], 500);
        }
    }
}