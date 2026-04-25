<?php

namespace App\Services;

class SidebarConfig
{
    public static function getMenuForRole(string $role): array
    {
        $config = [
            'doctor' => [
                'Medical' => [
                    ['name' => 'Dashboard', 'route' => 'doctor.dashboard', 'icon' => 'layout-dashboard'],
                    ['name' => 'Appointments', 'route' => 'appointments.index', 'icon' => 'calendar'],
                    ['name' => 'Schedule', 'route' => 'doctor.schedule', 'icon' => 'clock'],
                ],
            ],
            'receptionist' => [
                'Medical' => [
                    ['name' => 'Dashboard', 'route' => 'receptionist.dashboard', 'icon' => 'layout-dashboard'],
                    ['name' => 'Appointments', 'route' => 'appointments.index', 'icon' => 'calendar'],
                    ['name' => 'Book Appointment', 'route' => 'receptionist.book-appointment', 'icon' => 'calendar'],
                ],
            ],
            'patient' => [
                'Medical' => [
                    ['name' => 'Dashboard', 'route' => 'patient.dashboard', 'icon' => 'layout-dashboard'],
                    ['name' => 'Appointments', 'route' => 'appointments.index', 'icon' => 'calendar-days'],
                    ['name' => 'Book Appointment', 'route' => 'patient.book-appointment', 'icon' => 'calendar'],
                    ['name' => 'Medical Records', 'route' => 'patient.records', 'icon' => 'folder-medical'],
                ],
            ],
        ];

        return $config[$role] ?? [];
    }
}
