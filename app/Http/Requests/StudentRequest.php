<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\Student;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('id');

        $userId = null;
        if ($studentId) {
            $student = Student::find($studentId);
            $userId = $student ? $student->user_id : null;
        }

        return [
            'nim'              => ['required', 'string', 'max:20', Rule::unique('students', 'nim')->ignore($studentId)],
            'nama'             => 'required|string|max:100',
            'email'            => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'prodi'            => 'required|string|max:100',
            'angkatan'         => 'required|digits:4', // Karena tipe datanya YEAR
            'status_mahasiswa' => 'required|in:Aktif,Cuti,Lulus,DO', // Sesuai migration kamu
            'dosen_pa_id'      => 'nullable|exists:lecturers,id', // Harus ID dosen yang valid
            'ipk'              => 'nullable|numeric|min:0|max:4', // Range IPK 0.00 - 4.00
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