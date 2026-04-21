<?php

namespace App\Services;

class SidebarConfig
{
    public static function getMenuForRole(string $role): array
    {
        $config = [
            'clinic_admin' => [
                'Main' => [
                    ['name' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
                    ['name' => 'Staff Management', 'route' => 'admin.staff.index', 'icon' => 'users'],
                    ['name' => 'Doctor Management', 'route' => 'admin.doctors.index', 'icon' => 'user-plus'],
                    ['name' => 'Patient Management', 'route' => 'admin.patients.index', 'icon' => 'user-group'],
                    ['name' => 'Appointments', 'route' => 'admin.appointments.index', 'icon' => 'calendar'],
                    ['name' => 'Queue Management', 'route' => 'admin.queue.index', 'icon' => 'list-ordered'],
                    ['name' => 'Prescriptions', 'route' => 'admin.prescriptions.index', 'icon' => 'file-text'],
                    ['name' => 'Billing & Payments', 'route' => 'admin.billing.index', 'icon' => 'credit-card'],
                ],
            ],
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
