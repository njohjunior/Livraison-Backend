<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasFactory,HasApiTokens;
    protected $table = 'users';
    protected $fillable = [
        'nom',
        'email',
        'role',
        'password'
    ];
}
