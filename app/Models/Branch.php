<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Branch extends Authenticatable
{
    use HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'image',
        'bio',
        'status',
    ];

    protected $hidden = [
        'password',
    ];
}
