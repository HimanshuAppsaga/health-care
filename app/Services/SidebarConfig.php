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
                    ['name' => 'Appointments', 'route' => 'doctor.appointments.index', 'icon' => 'calendar'],
                    ['name' => 'Patients', 'route' => 'doctor.patients.index', 'icon' => 'user-group'],
                    ['name' => 'Prescriptions', 'route' => 'doctor.prescriptions.index', 'icon' => 'file-text'],
                    ['name' => 'Schedule', 'route' => 'doctor.schedule', 'icon' => 'clock'],
                ],
            ],
            'receptionist' => [
                'Medical' => [
                    ['name' => 'Dashboard', 'route' => 'receptionist.dashboard', 'icon' => 'layout-dashboard'],
                    ['name' => 'Patients', 'route' => 'receptionist.patients.index', 'icon' => 'user-group'],
                    ['name' => 'Appointments', 'route' => 'receptionist.appointments.index', 'icon' => 'calendar'],
                    ['name' => 'Queue', 'route' => 'receptionist.queue.index', 'icon' => 'list-ordered'],
                ],
            ],
            'patient' => [
                'Medical' => [
                    ['name' => 'Dashboard', 'route' => 'patient.dashboard', 'icon' => 'layout-dashboard'],
                    ['name' => 'Appointments', 'route' => 'patient.appointments.index', 'icon' => 'calendar'],
                    ['name' => 'Medical Records', 'route' => 'patient.records', 'icon' => 'folder-medical'],
                ],
            ],
        ];

        return $config[$role] ?? [];
    }
}
