<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Client extends User
{
    protected $table = 'users';

    // Sempre traz só usuários com permission_id = 5
    protected static function booted()
    {
        static::creating(function ($client) {
            $client->permission_id = 5; // força sempre grupo cliente
        });

        static::addGlobalScope('client', function (Builder $builder) {
            $builder->where('permission_id', 5);
        });
    }

    protected $fillable = [
        'tipo_pessoa',
        'name',
        'razao',
        'cpf',
        'cnpj',
        'email',
        'password',
        'status',
        'genero',
        'verificado',
        'permission_id',
        'config',
        'preferencias',
        'foto_perfil',
        'ativo',
        'autor',
        'token',
        'excluido',
        'reg_excluido',
        'deletado',
        'reg_deletado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
