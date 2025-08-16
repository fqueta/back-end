<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['title', 'icon', 'route'];

    public function permissions()
    {
        return $this->hasMany(MenuPermission::class);
    }
}
