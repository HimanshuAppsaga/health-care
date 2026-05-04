<?php

namespace Database\Seeders;

use App\Models\Clinic;
use Illuminate\Database\Seeder;

class ClinicApiKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update all existing clinics with a new API key if they don't have one
        Clinic::whereNull('api_key')
            ->orWhere('api_key', '')
            ->get()
            ->each(function (Clinic $clinic) {
                $clinic->update([
                    'api_key' => Clinic::generateUniqueApiKey(),
                ]);

                $this->command->info("Generated API Key for clinic: {$clinic->name}");
                $this->command->warn("API Key: {$clinic->api_key}");
            });
    }
}
