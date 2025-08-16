<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Menu;
use App\Models\MenuPermission;

class MenuPermissionSeeder extends Seeder
{
    public function run()
    {
        // Buscar permissões já criadas no PermissionSeeder
        $master       = Permission::where('name', 'Master')->first();
        $admin        = Permission::where('name', 'Administrador')->first();
        $gerente      = Permission::where('name', 'Gerente')->first();
        $escritorio   = Permission::where('name', 'Escritório')->first();

        // Master -> todos os menus
        $allMenus = Menu::all();
        foreach ($allMenus as $menu) {
            MenuPermission::create([
                'permission_id' => $master->id,
                'menu_id' => $menu->id,
            ]);
        }

        // Administrador -> todos menos algumas configs
        $restrictedConfigMenus = ['Permissões', 'Status de OS', 'Formas de Pagamento', 'Sistema'];
        $menusAdmin = Menu::whereDoesntHave('parent', function ($q) use ($restrictedConfigMenus) {
                $q->whereIn('title', $restrictedConfigMenus);
            })
            ->orWhereIn('title', ['Usuários', 'Perfis de Usuário'])
            ->get();

        foreach ($menusAdmin as $menu) {
            MenuPermission::create([
                'permission_id' => $admin->id,
                'menu_id' => $menu->id,
            ]);
        }

        // Gerente -> todos menos Configurações
        $menusGerente = Menu::whereDoesntHave('parent', function ($q) {
                $q->where('title', 'Configurações');
            })
            ->where('title', '!=', 'Configurações')
            ->get();

        foreach ($menusGerente as $menu) {
            MenuPermission::create([
                'permission_id' => $gerente->id,
                'menu_id' => $menu->id,
            ]);
        }

        // Escritório -> apenas Dashboard e Clientes
        $menusEscritorio = Menu::whereIn('title', ['Dashboard', 'Clientes'])->get();
        foreach ($menusEscritorio as $menu) {
            MenuPermission::create([
                'permission_id' => $escritorio->id,
                'menu_id' => $menu->id,
            ]);
        }
    }
}
