<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Criar um funil de teste
$funnel = App\Models\Funnel::create([
    'name' => 'Teste',
    'description' => 'Funil de teste',
    'color' => '#3b82f6',
    'isActive' => true
]);

echo "Funnel criado com ID: " . $funnel->id . "\n";

// Criar um stage de teste
$stage = App\Models\Stage::create([
    'name' => 'Etapa Teste',
    'description' => 'Etapa de teste',
    'color' => '#10b981',
    'funnel_id' => $funnel->id,
    'isActive' => true,
    'order' => 1,
    'settings' => [
        'autoAdvance' => true,
        'maxItems' => 10,
        'notifyOnEntry' => true,
        'notifyOnExit' => false,
        'requireApproval' => false,
        'timeLimit' => 24
    ]
]);

echo "Stage criado com ID: " . $stage->id . "\n";
echo "JSON do Stage:\n";
echo json_encode($stage->toArray(), JSON_PRETTY_PRINT);