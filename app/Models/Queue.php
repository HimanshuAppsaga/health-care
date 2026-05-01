<?php

namespace App\Models;

use App\Enums\QueueStatus;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'appointment_id',
        'token_number',
        'status',
        'called_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'status' => QueueStatus::class,
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
