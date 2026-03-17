<?php

namespace App\Services;

use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class LecturerService
{
    public function getAll()
    {
        return Lecturer::with('user')->get();
    }

    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            // 1. Buat Akun User
            $user = User::create([
                'name'      => $data['nama'], // Masuk ke kolom name
                'email'     => $data['email'],
                'password'  => Hash::make('password123'),
                // 'is_active' => true, // Uncomment jika tabel users kamu pakai is_active
            ]);

            // 2. Beri role Dosen
            $user->assignRole('Dosen');

            // 3. Buat Data Dosen (pakai field sesuai migrationmu)
            $lecturer = Lecturer::create([
                'user_id'      => $user->id,
                'nidn'         => $data['nidn'],
                'prodi'        => $data['prodi'],
                'status_dosen' => $data['status_dosen'],
            ]);

            DB::commit();
            return $lecturer->load('user');

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal menyimpan data Dosen: ' . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();

        try {
            $lecturer = Lecturer::find($id);
            if (!$lecturer) throw new Exception('Dosen tidak ditemukan.', 404);

            // 1. Update Data Dosen
            $lecturer->update([
                'nidn'         => $data['nidn'],
                'prodi'        => $data['prodi'],
                'status_dosen' => $data['status_dosen'],
            ]);

            // 2. Update Data User
            $lecturer->user->update([
                'name'  => $data['nama'],
                'email' => $data['email'],
            ]);

            DB::commit();
            return $lecturer->load('user');

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal memperbarui data Dosen: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        
        try {
            $lecturer = Lecturer::find($id);
            if (!$lecturer) throw new Exception('Dosen tidak ditemukan.', 404);

            $userId = $lecturer->user_id;
            $lecturer->delete();
            User::find($userId)->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal menghapus data Dosen: ' . $e->getMessage());
        }
    }
}