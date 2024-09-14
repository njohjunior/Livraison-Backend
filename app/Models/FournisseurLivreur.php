<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FournisseurLivreur extends Model
{
    use HasFactory;

    protected $table = 'fournisseur_livreurs';

    protected $fillable = ['fournisseur_id', 'livreur_id'];
    
}