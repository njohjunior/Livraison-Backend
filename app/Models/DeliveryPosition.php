<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'duration',
        'latitude',
        'longitude',
    ];

    // Relation avec le modèle Course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
