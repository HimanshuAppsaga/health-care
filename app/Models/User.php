<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomResetPassword;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'profile_photo_path', 'phone', 'password', 'is_active', 'bio', 'rating', 'role_id'])]
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

    public function ensureDoctorProfileExists(): ?Doctor
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

            $this->setRelation('doctor', $doctor);
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

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        Cache::put("password_reset_otp_{$this->email}", $otp, now()->addMinutes(config('auth.passwords.users.expire', 60)));
        $this->notify(new CustomResetPassword($token, $otp));
    }
}
