<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcademicPeriodRequest;
use App\Services\AcademicPeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AcademicPeriodController extends Controller
{
    protected $periodService;

    public function __construct(AcademicPeriodService $periodService)
    {
        $this->periodService = $periodService;
    }

    public function index()
    {
        try {
            $periods = $this->periodService->getAll();
            
            return response()->json([
                'status'  => 'success',
                'message' => 'Berhasil mengambil data periode',
                'data'    => $periods,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data',
                'data'    => [],
            ], 500);
        }
    }

    public function store(AcademicPeriodRequest $request)
    {
        try {
            $period = $this->periodService->create($request->validated());
            
            return response()->json([
                'status'  => 'success',
                'message' => 'Periode Akademik berhasil dibuat',
                'data'    => $period,
            ], 201);
            
        } catch (\Exception $e) {
            Log::critical('Create Period Error:', ["message" => $e->getMessage()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Internal Server Error',
                'data'    => [],
            ], 500);
        }
    }

    public function setActive($id)
    {
        try {
            $period = $this->periodService->setAsActive($id);
            
            return response()->json([
                'status'  => 'success',
                'message' => "Periode Semester {$period->semester} {$period->tahun_akademik} berhasil diaktifkan",
                'data'    => $period,
            ], 200);
            
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'data'    => [],
            ], $statusCode);
        }
    }

    public function destroy($id)
    {
        try {
            $this->periodService->delete($id);
            
            return response()->json([
                'status'  => 'success',
                'message' => 'Periode Akademik berhasil dihapus',
                'data'    => [],
            ], 200);
            
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'data'    => [],
            ], $statusCode);
        }
    }
}