<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use Illuminate\Support\Facades\DB;
use Exception;

class AcademicPeriodService
{
    public function getAll()
    {
        // Tampilkan dari yang paling baru dibuat
        return AcademicPeriod::orderBy('id', 'desc')->get();
    }

    public function create(array $data)
    {
        // By default, periode baru statusnya 'Draft'
        $data['status'] = 'Draft';
        return AcademicPeriod::create($data);
    }

    /**
     * Logic untuk mengaktifkan sebuah periode
     * @throws Exception
     */
    public function setAsActive($id)
    {
        // Pakai DB Transaction agar kalau gagal di tengah jalan, database kembali ke awal
        DB::beginTransaction();

        try {
            $period = AcademicPeriod::find($id);

            if (!$period) {
                throw new Exception('Periode Akademik tidak ditemukan.', 404);
            }

            if ($period->status === 'Aktif') {
                throw new Exception('Periode ini sudah berstatus Aktif.', 400);
            }

            // 1. Ubah SEMUA periode yang saat ini 'Aktif' menjadi 'Selesai'
            AcademicPeriod::where('status', 'Aktif')->update(['status' => 'Selesai']);

            // 2. Jadikan periode yang dipilih ini menjadi 'Aktif'
            $period->status = 'Aktif';
            $period->save();

            DB::commit();

            return $period;

        } catch (Exception $e) {
            DB::rollBack(); // Batalkan semua perubahan jika terjadi error
            throw $e;
        }
    }

    public function delete($id)
    {
        $period = AcademicPeriod::find($id);
        
        if (!$period) {
            throw new Exception('Periode Akademik tidak ditemukan.', 404);
        }

        if ($period->status !== 'Draft') {
            throw new Exception('Hanya periode berstatus Draft yang boleh dihapus.', 403);
        }

        return $period->delete();
    }
}