<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;

try {
    echo "Removendo categorias financeiras existentes...\n";
    
    $deleted = Category::where('entidade', 'financeiro')->delete();
    
    echo "Removidas {$deleted} categorias financeiras.\n";
    echo "Pronto para executar o seeder novamente.\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}