<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class StudentService
{
    public function getAll()
    {
        // Tarik relasi user (untuk nama mhs) DAN dosen_pa.user (untuk nama dosen)
        return Student::with(['user', 'dosen_pa.user'])
            ->orderBy('angkatan', 'desc')
            ->orderBy('nim', 'asc')
            ->get();
    }

    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name'      => $data['nama'],
                'email'     => $data['email'],
                'password'  => Hash::make('password123'),
            ]);

            $user->assignRole('Mahasiswa');

            $student = Student::create([
                'user_id'          => $user->id,
                'dosen_pa_id'      => $data['dosen_pa_id'] ?? null,
                'nim'              => $data['nim'],
                'prodi'            => $data['prodi'],
                'angkatan'         => $data['angkatan'],
                'status_mahasiswa' => $data['status_mahasiswa'],
                'ipk'              => $data['ipk'] ?? 0.00,
            ]);

            DB::commit();
            return $student->load(['user', 'dosen_pa.user']);

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal menyimpan data Mahasiswa: ' . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();

        try {
            $student = Student::find($id);
            if (!$student) throw new Exception('Mahasiswa tidak ditemukan.', 404);

            $student->update([
                'dosen_pa_id'      => $data['dosen_pa_id'] ?? null,
                'nim'              => $data['nim'],
                'prodi'            => $data['prodi'],
                'angkatan'         => $data['angkatan'],
                'status_mahasiswa' => $data['status_mahasiswa'],
                'ipk'              => $data['ipk'] ?? $student->ipk, // Biarkan IPK lama kalau tidak diisi
            ]);

            $student->user->update([
                'name'  => $data['nama'],
                'email' => $data['email'],
            ]);

            DB::commit();
            return $student->load(['user', 'dosen_pa.user']);

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal memperbarui data Mahasiswa: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        
        try {
            $student = Student::find($id);
            if (!$student) throw new Exception('Mahasiswa tidak ditemukan.', 404);

            $userId = $student->user_id;

            $student->delete();
            User::find($userId)->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal menghapus data Mahasiswa: ' . $e->getMessage());
        }
    }
}