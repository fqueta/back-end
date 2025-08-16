<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'avatar',
        'email',
        'password',
        'permission_id',
        'token',
    ];
     protected $casts = [
        'config' => 'array',
        'preferencias' => 'array',
    ];
     public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    public function menusPermitidos()
    {
        return Menu::whereHas('permissions', function ($q) {
            $q->where('permissions.id', $this->permission_id);
        })->get();
    }
    public function menusPermitidosFiltrados()
    {
        $menus = Menu::whereHas('permissions', function ($q) {
            $q->where('permissions.id', $this->permission_id);
        })->get();

        // Filtra também os submenus dentro do campo "items"
        $menusFiltrados = $menus->map(function ($menu) {
            if (is_array($menu->items) && !empty($menu->items)) {
                $menu->items = collect($menu->items)->filter(function ($submenu) {
                    // Aqui você pode colocar lógica adicional
                    // Ex: checar se o submenu exige permissão extra
                    return true;
                })->values()->toArray();
            }
            return $menu;
        });

        return $menusFiltrados;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
