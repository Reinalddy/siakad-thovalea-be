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
        // 1. Validasi Request
        $validateRequest = Validator::make($request->all(), [
            "email"    => "required|email",
            "password" => "required",
        ]);

        if ($validateRequest->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validateRequest->errors()->first(),
                'data'    => $validateRequest->errors(), // Ubah dari data() ke errors()
            ], 422); // Jangan lupa kasih HTTP status 422 untuk validasi gagal
        }

        // 2. Eksekusi Logika via Service
        try {
            // Panggil fungsi login dari service
            $result = $this->authService->login($request->email, $request->password);

            // 3. Response Sukses
            return response()->json([
                'status'  => 'success',
                'message' => 'User logged in successfully',
                'data'    => $result,
            ], 200);

        } catch (\Exception $e) {
            // Ambil status code dari Exception di Service (401 atau 404)
            // Jika tidak ada code (misal error syntax), set default ke 500
            $statusCode = $e->getCode() ?: 500;

            // Jika error 500 (Fatal Error), catat di Log::critical
            if ($statusCode == 500) {
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
                ], 500);
            }

            // Response untuk error 404 (Not Found) & 401 (Invalid Password)
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'data'    => [],
            ], $statusCode);
        }
    }
}