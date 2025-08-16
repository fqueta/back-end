<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Estrutura base das permissões
        $allPermissions = [
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
            "settings.system.view",
        ];

        // Master (tudo liberado)
        DB::table('permissions')->insert([
            'name' => 'Master',
            'description' => 'Desenvolvedores (acesso total)',
            'redirect_login' => '/home',
            'active' => 's',
            'id_menu' => json_encode($allPermissions),
        ]);

        // Administrador (restrito em algumas configs)
        $adminPermissions = array_diff($allPermissions, [
            "settings.permissions.view",
            "settings.system.view"
        ]);

        DB::table('permissions')->insert([
            'name' => 'Administrador',
            'description' => 'Administração do sistema (restrições em configurações)',
            'redirect_login' => '/home',
            'active' => 's',
            'id_menu' => json_encode(array_values($adminPermissions)),
        ]);

        // Gerente (sem menu de configurações)
        $gerentePermissions = array_filter($allPermissions, fn($perm) => !str_starts_with($perm, "settings."));

        DB::table('permissions')->insert([
            'name' => 'Gerente',
            'description' => 'Acesso completo exceto configurações',
            'redirect_login' => '/home',
            'active' => 's',
            'id_menu' => json_encode(array_values($gerentePermissions)),
        ]);

        // Escritório (apenas Dashboard e Clientes)
        $escritorioPermissions = [
            "dashboard.view",
            "clients.view",
        ];

        DB::table('permissions')->insert([
            'name' => 'Escritório',
            'description' => 'Somente Dashboard e Clientes',
            'redirect_login' => '/home',
            'active' => 's',
            'id_menu' => json_encode($escritorioPermissions),
        ]);
    }
}
