<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Services\StudentService;

class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function index()
    {
        $students = $this->studentService->getAll();
        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil mengambil data mahasiswa',
            'data'    => $students,
        ], 200);
    }

    public function store(StudentRequest $request)
    {
        try {
            $student = $this->studentService->create($request->validated());
            return response()->json([
                'status'  => 'success',
                'message' => 'Mahasiswa berhasil ditambahkan, Akun Login otomatis dibuat.',
                'data'    => $student,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(StudentRequest $request, $id)
    {
        try {
            $student = $this->studentService->update($id, $request->validated());
            return response()->json([
                'status'  => 'success',
                'message' => 'Data Mahasiswa dan Akun berhasil diperbarui',
                'data'    => $student,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->studentService->delete($id);
            return response()->json([
                'status'  => 'success',
                'message' => 'Profil dan Akun Mahasiswa berhasil dihapus permanen',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}