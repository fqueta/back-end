<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Menu::truncate();
        Schema::enableForeignKeyConstraints();

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

        foreach ($menus as $menu) {
            Menu::create([
                'title' => $menu['title'],
                'url'   => $menu['url'],
                'icon'  => $menu['icon'],
                'items' => $menu['items'] ? json_encode($menu['items']) : null
            ]);
        }

        $this->command->info('Menus iniciais cadastrados com sucesso!');
    }
}
