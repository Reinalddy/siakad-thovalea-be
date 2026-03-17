<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\Lecturer;

class LecturerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $lecturerId = $this->route('id');
        
        $userId = null;
        if ($lecturerId) {
            $lecturer = Lecturer::find($lecturerId);
            $userId = $lecturer ? $lecturer->user_id : null;
        }

        return [
            'nidn'         => ['required', 'string', 'max:20', Rule::unique('lecturers', 'nidn')->ignore($lecturerId)],
            'nama'         => 'required|string|max:100', // Untuk field 'name' di tabel users
            'email'        => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)], 
            'prodi'        => 'required|string|max:100', // Sesuai migration kamu
            'status_dosen' => 'required|in:Tetap,LB,Cuti', // Sesuai enum migration kamu
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => $validator->errors()->first(),
            'data'    => $validator->errors()
        ], 422));
    }
}