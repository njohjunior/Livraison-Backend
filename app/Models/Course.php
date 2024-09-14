<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $tables = "courses";
    protected $fillable = ([
        "fournisseur_id",
        "titre",
        "description",
        "adresseRamassage",
        "adresseLivraison",
        "emailClient",
        "typeDeCourse"
    ]);
}
