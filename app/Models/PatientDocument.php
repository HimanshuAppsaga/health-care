<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDocument extends Model
{
    protected $fillable = [
        'patient_id',
        'file_path',
        'file_type',
        'uploaded_at',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
