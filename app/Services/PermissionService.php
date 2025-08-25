<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuPermission;
use App\Models\User;

class PermissionService
{
    /**
     * Verifica se um usuário (via grupos) tem permissão para ação em uma chave.
     */
    public function can(User $user, string $routeName, string $action = 'view'): bool
    {
        // pega todos os grupos que o usuário pertence
        $groupIds = isset($user['permission_id']) ? $user['permission_id'] : 0;
        $campo = 'can_' . $action; // can_view, can_create, can_edit, can_delete, can_upload

        // se no seu caso for hasOne ou belongsTo, só trocar.
        $get_id_menu_by_url = $this->get_id_menu_by_url($routeName);
        // dd($get_id_menu_by_url);
        $perm = MenuPermission::where('permission_id', $groupIds)
                ->where('menu_id', $get_id_menu_by_url)
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
    }
    /**
     * metodo para loca
     */
    public function get_id_menu_by_url($rm){
        $url = $this->get_url_by_route($rm);
        // dd($rm);
        return Menu::where('url',$url)->first()->id;
    }
    private function get_url_by_route($name=''){
        $url = '';
        if($name=='api.permissions.index' || $name == 'api.permissions.update' || $name == 'api.permissions.show' || $name == 'api.permissions.store' || $name == 'api.permissions.destroy'){
            $url = '/settings/permissions';
        }
        return $url;
    }
}
