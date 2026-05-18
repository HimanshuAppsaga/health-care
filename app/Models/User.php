<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'profile_photo_path', 'phone', 'password', 'is_active', 'employee_id', 'department', 'joining_date', 'bio', 'emergency_contact_name', 'emergency_contact_phone', 'address', 'unit', 'supervisor_name', 'rating', 'last_login_at', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            return $this->role && in_array($this->role->name, $role);
        }

        return $this->role && $this->role->name === $role;
    }

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function ensureDoctorProfileExists(): Doctor
    {
        $doctor = $this->doctor;

        if (! $doctor && $this->hasRole('doctor')) {
            $clinic = Clinic::firstOrCreate(
                ['id' => 1],
                ['name' => 'Default Clinic', 'address' => 'Main Street']
            );

            $doctor = Doctor::create([
                'user_id' => $this->id,
                'clinic_id' => $clinic->id,
                'specialization' => 'General',
                'qualification' => 'MBBS',
                'experience_years' => 0,
                'consultation_fee' => 0,
            ]);
        }

        return $doctor;
    }

    public function getDashboardRouteName(): string
    {
        if ($this->hasRole('receptionist')) {
            return 'receptionist.dashboard';
        }

        if ($this->hasRole('patient')) {
            return 'patient.dashboard';
        }

        if ($this->hasRole('doctor')) {
            return 'doctor.dashboard';
        }

        return 'appointments.index'; // Fallback instead of login to avoid infinite redirect
    }
}
