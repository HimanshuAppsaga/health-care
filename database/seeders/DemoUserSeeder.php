<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clinic = \App\Models\Clinic::first() ?: \App\Models\Clinic::factory()->create([
            'name' => 'City General Hospital',
            'email' => 'contact@citygeneral.com',
        ]);

        $roles = [
            'clinic_admin' => ['email' => 'admin@clinic.com', 'phone' => '1111111111'],
            'doctor' => ['email' => 'doctor@clinic.com', 'phone' => '2222222222'],
            'receptionist' => ['email' => 'receptionist@clinic.com', 'phone' => '3333333333'],
            'patient' => ['email' => 'patient@clinic.com', 'phone' => '4444444444'],
        ];

        foreach ($roles as $roleName => $data) {
            $role = \App\Models\Role::where('name', $roleName)->first();
            
            if (!$role) {
                $role = \App\Models\Role::create(['name' => $roleName]);
            }

            $user = \App\Models\User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => 'Demo ' . ucwords(str_replace('_', ' ', $roleName)),
                    'clinic_id' => $clinic->id,
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'is_active' => true,
                    'phone' => $data['phone'],
                ]
            );

            if (!$user->roles()->where('role_id', $role->id)->exists()) {
                $user->roles()->attach($role);
            }
        }
    }
}
