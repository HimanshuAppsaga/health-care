<?php

namespace Database\Seeders;

use App\Models\Clinic;
use Illuminate\Database\Seeder;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Clinic::create([
            'name' => 'Demo Clinic',
            'address' => '123 Clinic St, City',
            'phone' => '1234567890',
            'email' => 'contact@democlinic.com',
            'timezone' => 'UTC',
            'subscription_plan' => 'Pro',
            'is_active' => true,
        ]);
    }
}
