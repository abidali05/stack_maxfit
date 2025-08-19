<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Coach extends Authenticatable
{
    protected $fillable = [
        'name', 'email','password', 'phone', 'image', 'bio'
    ];
}
