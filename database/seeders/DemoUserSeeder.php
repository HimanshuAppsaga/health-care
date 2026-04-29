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
        $users = [
            [
                'name' => 'Demo Doctor',
                'email' => 'doctor@clinic.com',
                'phone' => '2222222222',
                'role' => 'doctor'
            ],
            [
                'name' => 'Demo Receptionist',
                'email' => 'receptionist@clinic.com',
                'phone' => '3333333333',
                'role' => 'receptionist'
            ],
            [
                'name' => 'Demo Patient',
                'email' => 'patient@clinic.com',
                'phone' => '4444444444',
                'role' => 'patient'
            ],
            [
                'name' => 'Dr. Smith',
                'email' => 'smith@example.com',
                'phone' => '5555555555',
                'role' => 'doctor'
            ],
        ];

        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->first();

            if (! $role) {
                $role = Role::create(['name' => $userData['role']]);
            }

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'phone' => $userData['phone'],
                ]
            );

            if (! $user->roles()->where('role_id', $role->id)->exists()) {
                $user->roles()->attach($role);
            }
        }
    }
}
