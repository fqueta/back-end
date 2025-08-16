<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name', 'id_menu', 'redirect_login', 'config', 'description', 'guard_name',
        'active', 'autor', 'token', 'excluido', 'reg_excluido', 'deletado', 'reg_deletado'
    ];

    protected $casts = [
        'id_menu' => 'array',
        'config'  => 'array',
    ];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_permission');
    }
}
