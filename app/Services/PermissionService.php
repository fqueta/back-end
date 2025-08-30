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
        // dd($routeName);
        // se no seu caso for hasOne ou belongsTo, só trocar.
        $get_id_menu_by_url = $this->get_id_menu_by_url($routeName);
        // dd($routeName);
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
        return Menu::where('url',$url)->first()->id;
    }
    /**
     * Metodo para veriricar se o usuario tem permissão para executar ao acessar esse recurso atraves de ''
     * @params string 'view | create | edit | delete'
     */
    public function isHasPermission($permissao=''){
        $user = request()->user();
        $routeName = request()->route()->getName();
        // dd($routeName);
        if ($this->can($user, $routeName, $permissao)) {
            return true;
        }else{
            return false;
        }
    }
    private function get_url_by_route($name=''){
        $url = '';
        // dd($name);
        if($name=='api.permissions.index' || $name == 'api.permissions.update' || $name == 'api.permissions.show' || $name == 'api.permissions.store' || $name == 'api.permissions.destroy'){
            $url = '/settings/permissions';
        }
        if($name=='api.users.index' || $name == 'api.users.update' || $name == 'api.users.show' || $name == 'api.users.store' || $name == 'api.users.destroy'){
            $url = '/settings/users';
        }
        if($name=='api.metrics.index' || $name == 'api.metrics.update' || $name == 'api.metrics.show' || $name == 'api.metrics.store' || $name == 'api.metrics.destroy'){
            $url = '/settings/metrics';
        }
        if($name=='api.clients.index' || $name == 'api.clients.update' || $name == 'api.clients.show' || $name == 'api.clients.store' || $name == 'api.clients.destroy' || $name == 'api.clients.restore' || $name == 'api.clients.forceDelete'){
            $url = '/clients';
        }
        return $url;
    }
}
