<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions (Best practice dari Spatie)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat Role
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Admin BAAK']);
        Role::create(['name' => 'Dosen']);
        Role::create(['name' => 'Mahasiswa']);

        // 2. Buat Akun Super Admin (Biar kamu bisa langsung login)
        $superAdmin = User::create([
            'name'      => 'Reinalddy',
            'email'     => 'admin@thovalea.com',
            'password'  => Hash::make('password123'), // Password default
            'is_active' => true,
        ]);

        // 3. Assign Role ke Akun tersebut
        $superAdmin->assignRole('Super Admin');

        // Opsional: Buat satu akun Dosen untuk testing
        $dosen = User::create([
            'name'      => 'Siti Aminah, M.Kom',
            'email'     => 'dosen@thovalea.com',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ]);
        $dosen->assignRole('Dosen');

        // Opsional: Buat satu akun Mahasiswa untuk testing
        $mahasiswa = User::create([
            'name'      => 'Budi Santoso',
            'email'     => 'mahasiswa@thovalea.com',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ]);
        $mahasiswa->assignRole('Mahasiswa');
    }
}
