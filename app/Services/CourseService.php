<?php

namespace App\Services;

use App\Models\Course;
use Exception;

class CourseService
{
    public function getAll()
    {
        // Ambil semua mata kuliah, urutkan berdasarkan semester plot lalu nama
        return Course::orderBy('semester_plot', 'asc')->orderBy('nama_mk', 'asc')->get();
    }

    public function create(array $data)
    {
        return Course::create($data);
    }

    public function update($id, array $data)
    {
        $course = Course::find($id);

        if (!$course) {
            throw new Exception('Mata kuliah tidak ditemukan.', 404);
        }

        $course->update($data);
        return $course;
    }

    public function delete($id)
    {
        $course = Course::find($id);
        
        if (!$course) {
            throw new Exception('Mata kuliah tidak ditemukan.', 404);
        }

        // Opsional: Nanti bisa ditambah logic untuk mengecek apakah MK ini sedang dipakai di tabel Kelas/KRS
        // Jika dipakai, throw Exception tidak boleh dihapus.

        return $course->delete();
    }
}