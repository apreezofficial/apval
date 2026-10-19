<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = ['provider', 'code', 'name', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];
}
