<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentController extends BaseController
{
    /**
     * Store a newly created student in storage.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nim' => ['required', 'string', 'unique:students'],
            'batch' => ['required', 'integer', 'min:2000', 'max:2100'],
            'prodi_id' => ['required', 'exists:study_programs,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        try {
            DB::beginTransaction();

            // 1. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // 2. Assign Role "Student" (assuming Spatie permissions are seeded)
            $user->assignRole('Student');

            // 3. Create Student Profile
            $user->student()->create([
                'nim' => $request->nim,
                'batch' => $request->batch,
                'prodi_id' => $request->prodi_id,
            ]);

            DB::commit();

            // Load the newly created relationships to return a structured response
            $user->load(['student.studyProgram']);

            return $this->sendResponse(
                new UserResource($user),
                'Student registered successfully.',
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->sendError('Server Error', [], 500);
        }
    }
}
