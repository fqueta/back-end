<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OptionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('options')->insert([
            [
                'name'  => 'Id da permissão dos clientes',
                'value' => '5',
                'url'   => 'permission_client_id',
            ],
            [
                'name'  => 'Id da permissão dos fornecedores',
                'value' => '6',
                'url'   => 'permission_partner_id',
            ],
            [
                'name'  => 'Url importação Api Aeroclube',
                'value' => 'https://api.aeroclubejf.com.br/api/v1/metricas',
                'url'   => 'url_api_aeroclube',
            ],
            [
                'name'  => 'Token da Api Aeroclube',
                'value' => '',
                'url'   => 'token_api_aeroclube',
            ],
        ]);
    }
}
