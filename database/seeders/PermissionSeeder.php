<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('permissions')->delete();

        DB::table('permissions')->insert([
            // MASTER → acesso a tudo
            [
                'name' => 'Master',
                'description' => 'Desenvolvedores',
                'redirect_login' => '/home',
                'active' => 's',
                'id_menu' => json_encode([
                    "dashboard.view",
                    "clients.view",
                    "service-objects.view",
                    "catalog.view",
                    "catalog.products.view",
                    "catalog.services.view",
                    "catalog.categories.view",
                    "budgets.view",
                    "service-orders.view",
                    "finance.view",
                    "finance.payments.view",
                    "finance.cash-flow.view",
                    "finance.accounts-receivable.view",
                    "finance.accounts-payable.view",
                    "reports.view",
                    "reports.revenue.view",
                    "reports.service-orders.view",
                    "reports.top-products.view",
                    "reports.financial.view",
                    "settings.view",
                    "settings.users.view",
                    "settings.user-profiles.view",
                    "settings.permissions.view",
                    "settings.os-statuses.view",
                    "settings.payment-methods.view",
                    "settings.system.view"
                ]),
            ],

            // ADMINISTRADOR → tudo, mas em configurações só "Usuários" e "Perfis"
            [
                'name' => 'Administrador',
                'description' => 'Administradores do sistema',
                'redirect_login' => '/home',
                'active' => 's',
                'id_menu' => json_encode([
                    "dashboard.view",
                    "clients.view",
                    "service-objects.view",
                    "catalog.view",
                    "catalog.products.view",
                    "catalog.services.view",
                    "catalog.categories.view",
                    "budgets.view",
                    "service-orders.view",
                    "finance.view",
                    "finance.payments.view",
                    "finance.cash-flow.view",
                    "finance.accounts-receivable.view",
                    "finance.accounts-payable.view",
                    "reports.view",
                    "reports.revenue.view",
                    "reports.service-orders.view",
                    "reports.top-products.view",
                    "reports.financial.view",
                    "settings.view",
                    "settings.users.view",
                    "settings.user-profiles.view"
                ]),
            ],

            // GERENTE → todos os menus exceto configurações
            [
                'name' => 'Gerente',
                'description' => 'Gerente do sistema (sem acesso a configurações)',
                'redirect_login' => '/home',
                'active' => 's',
                'id_menu' => json_encode([
                    "dashboard.view",
                    "clients.view",
                    "service-objects.view",
                    "catalog.view",
                    "catalog.products.view",
                    "catalog.services.view",
                    "catalog.categories.view",
                    "budgets.view",
                    "service-orders.view",
                    "finance.view",
                    "finance.payments.view",
                    "finance.cash-flow.view",
                    "finance.accounts-receivable.view",
                    "finance.accounts-payable.view",
                    "reports.view",
                    "reports.revenue.view",
                    "reports.service-orders.view",
                    "reports.top-products.view",
                    "reports.financial.view"
                ]),
            ],

            // ESCRITÓRIO → somente dois primeiros menus
            [
                'name' => 'Escritório',
                'description' => 'Acesso limitado a Dashboard e Clientes',
                'redirect_login' => '/home',
                'active' => 's',
                'id_menu' => json_encode([
                    "dashboard.view",
                    "clients.view"
                ]),
            ],
        ]);
    }
}
