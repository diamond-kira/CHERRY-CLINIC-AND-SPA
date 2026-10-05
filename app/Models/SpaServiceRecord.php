<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpaServiceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'therapist_id',
        'status',
        'treatment_notes',
        'products_used',
        'recommendations',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'products_used' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'therapist_id');
    }
}
