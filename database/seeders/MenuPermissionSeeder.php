<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\MenuPermission;

class MenuPermissionSeeder extends Seeder
{
    public function run()
    {
        // Desativar FK checks na conexão tenant
        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0;');

        // Apagar pivot primeiro
        MenuPermission::truncate();

        // Apagar menus e permissions
        Menu::truncate();
        Permission::truncate();

        // Ativar novamente FK checks
        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1;');

        // Definir menus
        $menus = [
            [
                'title' => 'Dashboard',
                'url'   => '/',
                'icon'  => 'Home',
                'items' => null,
            ],
            [
                'title' => 'Clientes',
                'url'   => '/clients',
                'icon'  => 'Users',
                'items' => null,
            ],
            [
                'title' => 'Objetos do Serviço',
                'url'   => '/service-objects',
                'icon'  => 'Wrench',
                'items' => null,
            ],
            [
                'title' => 'Catálogo',
                'url'   => null,
                'icon'  => 'Package',
                'items' => [
                    [ 'title' => 'Produtos', 'url' => '/products' ],
                    [ 'title' => 'Serviços', 'url' => '/services' ],
                    [ 'title' => 'Categorias', 'url' => '/categories' ],
                ],
            ],
            [
                'title' => 'Orçamentos',
                'url'   => '/budgets',
                'icon'  => 'FileText',
                'items' => null,
            ],
            [
                'title' => 'Ordens de Serviço',
                'url'   => '/service-orders',
                'icon'  => 'ClipboardList',
                'items' => null,
            ],
            [
                'title' => 'Financeiro',
                'url'   => null,
                'icon'  => 'DollarSign',
                'items' => [
                    [ 'title' => 'Pagamentos', 'url' => '/payments' ],
                    [ 'title' => 'Fluxo de Caixa', 'url' => '/cash-flow' ],
                    [ 'title' => 'Contas a Receber', 'url' => '/accounts-receivable' ],
                    [ 'title' => 'Contas a Pagar', 'url' => '/accounts-payable' ],
                ],
            ],
            [
                'title' => 'Relatórios',
                'url'   => null,
                'icon'  => 'BarChart3',
                'items' => [
                    [ 'title' => 'Faturamento', 'url' => '/reports/revenue' ],
                    [ 'title' => 'OS por Período', 'url' => '/reports/service-orders' ],
                    [ 'title' => 'Produtos Mais Vendidos', 'url' => '/reports/top-products' ],
                    [ 'title' => 'Análise Financeira', 'url' => '/reports/financial' ],
                ],
            ],
            [
                'title' => 'Configurações',
                'url'   => null,
                'icon'  => 'Settings',
                'items' => [
                    [ 'title' => 'Usuários', 'url' => '/settings/users' ],
                    [ 'title' => 'Perfis de Usuário', 'url' => '/settings/user-profiles' ],
                    [ 'title' => 'Permissões', 'url' => '/settings/permissions' ],
                    [ 'title' => 'Status de OS', 'url' => '/settings/os-statuses' ],
                    [ 'title' => 'Formas de Pagamento', 'url' => '/settings/payment-methods' ],
                    [ 'title' => 'Sistema', 'url' => '/settings/system' ],
                ],
            ],
        ];

        // Inserir menus
        foreach ($menus as $menuData) {
            $menu = Menu::create([
                'title' => $menuData['title'],
                'url'   => $menuData['url'],
                'icon'  => $menuData['icon'],
                'items' => $menuData['items'] ? json_encode($menuData['items']) : null,
            ]);

            // Criar permissão associada
            $permission = Permission::create([
                'name' => 'view_' . \Str::slug($menuData['title'], '_'),
                'guard_name' => 'web',
            ]);

            // Associar menu e permissão
            MenuPermission::create([
                'menu_id' => $menu->id,
                'permission_id' => $permission->id,
            ]);
        }
    }
}
