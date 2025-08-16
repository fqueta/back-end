<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run()
    {
        // Dashboard
        Menu::create([
            'title' => 'Dashboard',
            'url'   => '/',
            'icon'  => 'Home',
        ]);

        // Clientes
        Menu::create([
            'title' => 'Clientes',
            'url'   => '/clients',
            'icon'  => 'Users',
        ]);

        // Objetos do Serviço
        Menu::create([
            'title' => 'Objetos do Serviço',
            'url'   => '/service-objects',
            'icon'  => 'Wrench',
        ]);

        // ----------------------------
        // Catálogo (pai + filhos)
        // ----------------------------
        $catalogo = Menu::create([
            'title' => 'Catálogo',
            'url'   => null,
            'icon'  => 'Package',
        ]);

        Menu::create([
            'title' => 'Produtos',
            'url'   => '/products',
            'parent_id' => $catalogo->id,
        ]);

        Menu::create([
            'title' => 'Serviços',
            'url'   => '/services',
            'parent_id' => $catalogo->id,
        ]);

        Menu::create([
            'title' => 'Categorias',
            'url'   => '/categories',
            'parent_id' => $catalogo->id,
        ]);

        // Orçamentos
        Menu::create([
            'title' => 'Orçamentos',
            'url'   => '/budgets',
            'icon'  => 'FileText',
        ]);

        // Ordens de Serviço
        Menu::create([
            'title' => 'Ordens de Serviço',
            'url'   => '/service-orders',
            'icon'  => 'ClipboardList',
        ]);

        // ----------------------------
        // Financeiro (pai + filhos)
        // ----------------------------
        $financeiro = Menu::create([
            'title' => 'Financeiro',
            'url'   => null,
            'icon'  => 'DollarSign',
        ]);

        Menu::create([
            'title' => 'Pagamentos',
            'url'   => '/payments',
            'parent_id' => $financeiro->id,
        ]);

        Menu::create([
            'title' => 'Fluxo de Caixa',
            'url'   => '/cash-flow',
            'parent_id' => $financeiro->id,
        ]);

        Menu::create([
            'title' => 'Contas a Receber',
            'url'   => '/accounts-receivable',
            'parent_id' => $financeiro->id,
        ]);

        Menu::create([
            'title' => 'Contas a Pagar',
            'url'   => '/accounts-payable',
            'parent_id' => $financeiro->id,
        ]);

        // ----------------------------
        // Relatórios (pai + filhos)
        // ----------------------------
        $relatorios = Menu::create([
            'title' => 'Relatórios',
            'url'   => null,
            'icon'  => 'BarChart3',
        ]);

        Menu::create([
            'title' => 'Faturamento',
            'url'   => '/reports/revenue',
            'parent_id' => $relatorios->id,
        ]);

        Menu::create([
            'title' => 'OS por Período',
            'url'   => '/reports/service-orders',
            'parent_id' => $relatorios->id,
        ]);

        Menu::create([
            'title' => 'Produtos Mais Vendidos',
            'url'   => '/reports/top-products',
            'parent_id' => $relatorios->id,
        ]);

        Menu::create([
            'title' => 'Análise Financeira',
            'url'   => '/reports/financial',
            'parent_id' => $relatorios->id,
        ]);

        // ----------------------------
        // Configurações (pai + filhos)
        // ----------------------------
        $configuracoes = Menu::create([
            'title' => 'Configurações',
            'url'   => null,
            'icon'  => 'Settings',
        ]);

        Menu::create([
            'title' => 'Usuários',
            'url'   => '/settings/users',
            'parent_id' => $configuracoes->id,
        ]);

        Menu::create([
            'title' => 'Perfis de Usuário',
            'url'   => '/settings/user-profiles',
            'parent_id' => $configuracoes->id,
        ]);

        Menu::create([
            'title' => 'Permissões',
            'url'   => '/settings/permissions',
            'parent_id' => $configuracoes->id,
        ]);

        Menu::create([
            'title' => 'Status de OS',
            'url'   => '/settings/os-statuses',
            'parent_id' => $configuracoes->id,
        ]);

        Menu::create([
            'title' => 'Formas de Pagamento',
            'url'   => '/settings/payment-methods',
            'parent_id' => $configuracoes->id,
        ]);

        Menu::create([
            'title' => 'Sistema',
            'url'   => '/settings/system',
            'parent_id' => $configuracoes->id,
        ]);
    }
}
