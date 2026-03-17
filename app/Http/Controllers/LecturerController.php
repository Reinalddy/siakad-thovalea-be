<?php

namespace App\Http\Controllers;

use App\Http\Requests\LecturerRequest;
use App\Services\LecturerService;
use Illuminate\Http\Request;

class LecturerController extends Controller
{
    protected $lecturerService;

    public function __construct(LecturerService $lecturerService)
    {
        $this->lecturerService = $lecturerService;
    }

    public function index()
    {
        $lecturers = $this->lecturerService->getAll();
        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil mengambil data dosen',
            'data'    => $lecturers,
        ], 200);
    }

    public function store(LecturerRequest $request)
    {
        try {
            $lecturer = $this->lecturerService->create($request->validated());
            return response()->json([
                'status'  => 'success',
                'message' => 'Dosen berhasil ditambahkan, Akun Login otomatis dibuat.',
                'data'    => $lecturer,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(LecturerRequest $request, $id)
    {
        try {
            $lecturer = $this->lecturerService->update($id, $request->validated());
            return response()->json([
                'status'  => 'success',
                'message' => 'Data Dosen dan Akun berhasil diperbarui',
                'data'    => $lecturer,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->lecturerService->delete($id);
            return response()->json([
                'status'  => 'success',
                'message' => 'Profil dan Akun Dosen berhasil dihapus permanen',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}