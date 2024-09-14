<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Fournisseur extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'fournisseurs';
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'adresse',
        'contact',
        'password'
    ];

}
