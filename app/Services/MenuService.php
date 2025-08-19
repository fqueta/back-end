<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Str;

class MenuService
{
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
            'title'      => $menu->title,
            'url'        => $menu->url,
            'icon'       => $menu->icon,
            'permission' => $this->generatePermission($menu),
        ];

        if ($menu->children->isNotEmpty()) {
            $data['items'] = $menu->children->map(function ($child) {
                return $this->mapMenu($child);
            })->toArray();
        }

        return $data;
    }

    private function generatePermission(Menu $menu): string
    {
        // Exemplo: "settings.users" -> "settings.users.view"
        // baseando-se no "url"
        if ($menu->url) {
            $lurl = $menu->url;
            if($lurl=='/'){
                $lurl = isset($menu->title) ? $menu->title : 'dashboard';
                $lurl = strtolower($lurl);
            }
            $slug = Str::of($lurl)->trim('/')
                ->replace('/', '.'); // "/settings/users" -> "settings.users"
        } else {
            $slug = Str::slug($menu->title, '.'); // fallback no título
        }
        return $slug . '.view';
    }
}
