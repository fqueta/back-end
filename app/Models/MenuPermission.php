<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuPermission extends Model
{
    use HasFactory;

    protected $table = 'menu_permission';

    protected $fillable = [
        'permission_id',
        'menu_id',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
        'can_upload',
    ];

    /**
     * Relacionamento com a permissão (perfil)
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    /**
     * Relacionamento com o menu
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
