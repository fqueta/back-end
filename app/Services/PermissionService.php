<?php

namespace App\Services;

use App\Models\MenuPermission;
use App\Models\User;

class PermissionService
{
    /**
     * Verifica se um usuário (via grupos) tem permissão para ação em uma chave.
     */
    public function can(User $user, string $permissionKey, string $action = 'view'): bool
    {
        // pega todos os grupos que o usuário pertence
        $groupIds = isset($user['permission_id']) ? $user['permission_id'] : 0;
        $campo = 'can_' . $action; // can_view, can_create, can_edit, can_delete, can_upload

        // se no seu caso for hasOne ou belongsTo, só trocar.
        //   dd($permissionKey, $groupIds, $campo);

        $perm = MenuPermission::where('permission_id', $groupIds)
                              ->where('permission_key', $permissionKey)
                            //   ->where($campo,1)
                              ->first();
                            if (!$perm) {
                                return false;
                            }
        if(isset($perm[$campo]) && $perm[$campo]){
            return true;
        }else{
            return false;
        }

        // return match ($action) {
        //     'view'   => (bool) $perm['can_view'],
        //     'create' => (bool) $perm['can_create'],
        //     'edit'   => (bool) $perm['can_edit'],
        //     'delete' => (bool) $perm['can_delete'],
        //     'upload' => (bool) $perm['can_upload'],
        //     default  => false,
        // };
    }
}
