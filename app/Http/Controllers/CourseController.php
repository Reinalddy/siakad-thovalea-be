<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index()
    {
        $courses = $this->courseService->getAll();
        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil mengambil data mata kuliah',
            'data'    => $courses,
        ], 200);
    }

    public function store(CourseRequest $request)
    {
        try {
            $course = $this->courseService->create($request->validated());
            return response()->json([
                'status'  => 'success',
                'message' => 'Mata kuliah berhasil ditambahkan',
                'data'    => $course,
            ], 201);
        } catch (\Exception $e) {
            Log::critical("Create Course Error:", ["message" => $e->getMessage(), "line" => $e->getLine(), "file" => $e->getFile()]);
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }

    }

    public function update(CourseRequest $request, $id)
    {
        try {
            $course = $this->courseService->update($id, $request->validated());
            return response()->json([
                'status'  => 'success',
                'message' => 'Mata kuliah berhasil diperbarui',
                'data'    => $course,
            ], 200);
        } catch (\Exception $e) {
            Log::critical('update course error', ['message'=> $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->courseService->delete($id);
            return response()->json([
                'status'  => 'success',
                'message' => 'Mata kuliah berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            Log::critical('delete course error', ['message'=> $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }
}