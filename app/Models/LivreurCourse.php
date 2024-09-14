<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LivreurCourse extends Model
{
    use HasFactory;

    protected $table = 'livreur_courses';

    protected $fillable = [
        'course_id',
        'livreur_id',
    ];
}
