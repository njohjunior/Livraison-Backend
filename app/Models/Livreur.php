<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livreur extends Model
{
    use HasFactory;

    protected $table = 'livreurs';
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'adresse',
        'contact',
        'typeDeVehicule',
        'password'
    ];
    
}