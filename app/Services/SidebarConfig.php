<?php

namespace App\Services;

class SidebarConfig
{
    public static function getMenuForRole(string $role): array
    {
        $config = [
            'clinic_admin' => [
                'Management' => [
                    ['name' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
                    ['name' => 'Staff', 'route' => 'admin.staff.index', 'icon' => 'users'],
                    ['name' => 'Doctors', 'route' => 'admin.doctors.index', 'icon' => 'user-plus'],
                ],
                'Medical' => [
                    ['name' => 'Patients', 'route' => 'admin.patients.index', 'icon' => 'user-group'],
                    ['name' => 'Appointments', 'route' => 'admin.appointments.index', 'icon' => 'calendar'],
                    ['name' => 'Queue', 'route' => 'admin.queue.index', 'icon' => 'list-ordered'],
                    ['name' => 'Prescriptions', 'route' => 'admin.prescriptions.index', 'icon' => 'file-text'],
                ],
                'System' => [
                    ['name' => 'Billing', 'route' => 'admin.billing.index', 'icon' => 'credit-card'],
                    ['name' => 'Notifications', 'route' => 'admin.notifications.index', 'icon' => 'bell', 'badge' => 'unread_notifications_count'],
                    ['name' => 'Reports', 'route' => 'admin.reports.index', 'icon' => 'bar-chart-3'],
                    ['name' => 'Settings', 'route' => 'admin.settings', 'icon' => 'settings'],
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
                'System' => [
                    ['name' => 'Notifications', 'route' => 'doctor.notifications.index', 'icon' => 'bell', 'badge' => 'unread_notifications_count'],
                    ['name' => 'Profile', 'route' => 'doctor.profile', 'icon' => 'user'],
                ],
            ],
            'receptionist' => [
                'Medical' => [
                    ['name' => 'Dashboard', 'route' => 'receptionist.dashboard', 'icon' => 'layout-dashboard'],
                    ['name' => 'Patients', 'route' => 'receptionist.patients.index', 'icon' => 'user-group'],
                    ['name' => 'Appointments', 'route' => 'receptionist.appointments.index', 'icon' => 'calendar'],
                    ['name' => 'Queue', 'route' => 'receptionist.queue.index', 'icon' => 'list-ordered'],
                ],
                'System' => [
                    ['name' => 'Billing', 'route' => 'receptionist.billing.index', 'icon' => 'credit-card'],
                    ['name' => 'Notifications', 'route' => 'receptionist.notifications.index', 'icon' => 'bell', 'badge' => 'unread_notifications_count'],
                    ['name' => 'Profile', 'route' => 'receptionist.profile', 'icon' => 'user'],
                ],
            ],
            'patient' => [
                'Medical' => [
                    ['name' => 'Dashboard', 'route' => 'patient.dashboard', 'icon' => 'layout-dashboard'],
                    ['name' => 'Appointments', 'route' => 'patient.appointments.index', 'icon' => 'calendar'],
                    ['name' => 'Medical Records', 'route' => 'patient.records', 'icon' => 'folder-medical'],
                    ['name' => 'Documents', 'route' => 'patient.documents', 'icon' => 'files'],
                    ['name' => 'Queue Status', 'route' => 'patient.queue', 'icon' => 'activity'],
                ],
                'System' => [
                    ['name' => 'Notifications', 'route' => 'patient.notifications.index', 'icon' => 'bell', 'badge' => 'unread_notifications_count'],
                    ['name' => 'Profile', 'route' => 'patient.profile', 'icon' => 'user'],
                    ['name' => 'Settings', 'route' => 'patient.settings', 'icon' => 'settings'],
                ],
            ],
        ];

        return $config[$role] ?? [];
    }

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
}
