<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'document',
        'zip_code',
        'address',
        'city',
        'state',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
