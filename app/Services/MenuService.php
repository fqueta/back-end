<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuPermission;
use Illuminate\Support\Str;

class MenuService
{
    private $permission_id;
    public function __construct($permission_id=0)
    {
        $this->permission_id = $permission_id;
    }
    public function getMenuStructure(): array
    {
        $menus = Menu::whereNull('parent_id')->with('children')->get();

        return $menus->map(function ($menu) {
            return $this->mapMenu($menu);
        })->toArray();
    }

    private function mapMenu(Menu $menu): array
    {
        $data = [
            'id'         => $menu->id,
            'title'      => $menu->title,
            'parent_id'  => $menu->parent_id,
            'url'        => $menu->url,
            'icon'       => $menu->icon,
            'can_view' => $this->generatePermission($menu),
        ];

        if ($menu->children->isNotEmpty()) {
            $data['items'] = $menu->children->map(function ($child) {
                return $this->mapMenu($child);
            })->toArray();
        }

        return $data;
    }

    private function generatePermission(Menu $menu)
    {
        // Exemplo: "settings.users" -> "settings.users.view"
        // baseando-se no "url"
        // dd($menu);
        $permission_id = $this->permission_id;
        if ($menu->url) {
            $lurl = $menu->url;
            $dp = MenuPermission::where('menu_id', $menu->id)
                ->where('permission_id', $permission_id)
                ->first();
            if($dp && isset($dp['can_view'])){
                return $dp['can_view'];
            }else{
                return false;
            }
            // if($lurl=='/'){
            //     $lurl = isset($menu->title) ? $menu->title : 'dashboard';
            //     $lurl = strtolower($lurl);
            // }
            // $slug = Str::of($lurl)->trim('/')
                // ->replace('/', '.'); // "/settings/users" -> "settings.users"
        }
        // else {
        //     $slug = Str::slug($menu->title, '.'); // fallback no título
        // }
        // return $slug . '.view';
    }
}
