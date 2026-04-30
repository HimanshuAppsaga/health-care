<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'address',
        'api_key',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'api_key',
    ];

    /**
     * Generate a secure, unique API key for the clinic.
     */
    public static function generateUniqueApiKey(): string
    {
        do {
            $key = bin2hex(random_bytes(32)); // 64 characters
        } while (static::where('api_key', $key)->exists());

        return $key;
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
