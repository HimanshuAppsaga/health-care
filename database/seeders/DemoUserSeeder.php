<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $roles = [
            'doctor' => ['email' => 'doctor@clinic.com', 'phone' => '2222222222'],
            'receptionist' => ['email' => 'receptionist@clinic.com', 'phone' => '3333333333'],
            'patient' => ['email' => 'patient@clinic.com', 'phone' => '4444444444'],
        ];

        foreach ($roles as $roleName => $data) {
            $role = Role::where('name', $roleName)->first();

            if (! $role) {
                $role = Role::create(['name' => $roleName]);
            }

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => 'Demo '.ucwords(str_replace('_', ' ', $roleName)),
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'phone' => $data['phone'],
                ]
            );

            if (! $user->roles()->where('role_id', $role->id)->exists()) {
                $user->roles()->attach($role);
            }
        }
    }
}
