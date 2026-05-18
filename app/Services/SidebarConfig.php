<?php

namespace App\Services;

class SidebarConfig
{
    public static function getMenuForRole(string $role): array
    {
        $user = auth()->user();
        $clinicId = $user?->doctor?->clinic_id ?? ($user?->receptionist?->clinic_id ?? 0);

        $config = [
            'doctor' => [
                'Medical' => [
                    ['name' => 'Dashboard', 'route' => 'doctor.dashboard', 'icon' => 'layout-dashboard'],
                    ['name' => 'Appointments', 'route' => 'appointments.index', 'icon' => 'calendar'],
                ],
            ],
            'receptionist' => [
                'Medical' => [
                    ['name' => 'Dashboard', 'route' => 'receptionist.dashboard', 'icon' => 'layout-dashboard'],
                    ['name' => 'Appointments', 'route' => 'appointments.index', 'icon' => 'calendar'],
                    ['name' => 'Book Appointment', 'route' => 'receptionist.book-appointment', 'icon' => 'calendar'],
                ],
            ],
        ];

        return $config[$role] ?? [];
    }
}
