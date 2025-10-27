<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Funnel;
use App\Models\Stage;

/**
 * Seeder para criar funnels e stages de exemplo
 */
class FunnelStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar Funil de Vendas
        $salesFunnel = Funnel::create([
            'name' => 'Funil de Vendas',
            'description' => 'Funil principal para gerenciar o processo de vendas de serviços de manutenção de aeronaves',
            'color' => '#10b981',
            'isActive' => true,
            'settings' => [
                'autoAdvance' => false,
                'requiresApproval' => true,
                'notificationEnabled' => true
            ]
        ]);

        // Criar stages para o Funil de Vendas
        $salesStages = [
            [
                'name' => 'Lead',
                'description' => 'Cliente em potencial identificado',
                'color' => '#6b7280',
                'order' => 1,
                'settings' => [
                    'autoAdvanceAfterDays' => null,
                    'requiresDocuments' => false
                ]
            ],
            [
                'name' => 'Qualificação',
                'description' => 'Verificação da necessidade e capacidade do cliente',
                'color' => '#3b82f6',
                'order' => 2,
                'settings' => [
                    'autoAdvanceAfterDays' => 3,
                    'requiresDocuments' => true
                ]
            ],
            [
                'name' => 'Proposta',
                'description' => 'Elaboração e envio da proposta comercial',
                'color' => '#f59e0b',
                'order' => 3,
                'settings' => [
                    'autoAdvanceAfterDays' => 7,
                    'requiresDocuments' => true
                ]
            ],
            [
                'name' => 'Negociação',
                'description' => 'Discussão de termos e condições',
                'color' => '#ef4444',
                'order' => 4,
                'settings' => [
                    'autoAdvanceAfterDays' => 5,
                    'requiresDocuments' => false
                ]
            ],
            [
                'name' => 'Fechamento',
                'description' => 'Assinatura do contrato e início dos serviços',
                'color' => '#10b981',
                'order' => 5,
                'settings' => [
                    'autoAdvanceAfterDays' => null,
                    'requiresDocuments' => true
                ]
            ]
        ];

        foreach ($salesStages as $stageData) {
            Stage::create(array_merge($stageData, [
                'funnel_id' => $salesFunnel->id,
                'isActive' => true
            ]));
        }

        // Criar Funil de Manutenção
        $maintenanceFunnel = Funnel::create([
            'name' => 'Funil de Manutenção',
            'description' => 'Processo de acompanhamento das ordens de serviço de manutenção',
            'color' => '#8b5cf6',
            'isActive' => true,
            'settings' => [
                'autoAdvance' => true,
                'requiresApproval' => false,
                'notificationEnabled' => true
            ]
        ]);

        // Criar stages para o Funil de Manutenção
        $maintenanceStages = [
            [
                'name' => 'Recebido',
                'description' => 'Aeronave recebida para manutenção',
                'color' => '#6b7280',
                'order' => 1,
                'settings' => [
                    'autoAdvanceAfterDays' => 1,
                    'requiresInspection' => true
                ]
            ],
            [
                'name' => 'Inspeção',
                'description' => 'Avaliação técnica e diagnóstico',
                'color' => '#3b82f6',
                'order' => 2,
                'settings' => [
                    'autoAdvanceAfterDays' => 2,
                    'requiresInspection' => true
                ]
            ],
            [
                'name' => 'Aguardando Peças',
                'description' => 'Aguardando chegada de peças e materiais',
                'color' => '#f59e0b',
                'order' => 3,
                'settings' => [
                    'autoAdvanceAfterDays' => null,
                    'requiresInspection' => false
                ]
            ],
            [
                'name' => 'Em Manutenção',
                'description' => 'Execução dos serviços de manutenção',
                'color' => '#ef4444',
                'order' => 4,
                'settings' => [
                    'autoAdvanceAfterDays' => null,
                    'requiresInspection' => false
                ]
            ],
            [
                'name' => 'Teste',
                'description' => 'Testes finais e verificação de qualidade',
                'color' => '#8b5cf6',
                'order' => 5,
                'settings' => [
                    'autoAdvanceAfterDays' => 1,
                    'requiresInspection' => true
                ]
            ],
            [
                'name' => 'Concluído',
                'description' => 'Manutenção finalizada e aeronave liberada',
                'color' => '#10b981',
                'order' => 6,
                'settings' => [
                    'autoAdvanceAfterDays' => null,
                    'requiresInspection' => false
                ]
            ]
        ];

        foreach ($maintenanceStages as $stageData) {
            Stage::create(array_merge($stageData, [
                'funnel_id' => $maintenanceFunnel->id,
                'isActive' => true
            ]));
        }

        // Criar Funil de Suporte
        $supportFunnel = Funnel::create([
            'name' => 'Funil de Suporte',
            'description' => 'Processo de atendimento e resolução de solicitações de suporte',
            'color' => '#06b6d4',
            'isActive' => true,
            'settings' => [
                'autoAdvance' => false,
                'requiresApproval' => false,
                'notificationEnabled' => true
            ]
        ]);

        // Criar stages para o Funil de Suporte
        $supportStages = [
            [
                'name' => 'Aberto',
                'description' => 'Solicitação de suporte recebida',
                'color' => '#6b7280',
                'order' => 1,
                'settings' => [
                    'autoAdvanceAfterDays' => null,
                    'priority' => 'medium'
                ]
            ],
            [
                'name' => 'Em Análise',
                'description' => 'Equipe técnica analisando a solicitação',
                'color' => '#3b82f6',
                'order' => 2,
                'settings' => [
                    'autoAdvanceAfterDays' => 1,
                    'priority' => 'medium'
                ]
            ],
            [
                'name' => 'Em Andamento',
                'description' => 'Solução sendo implementada',
                'color' => '#f59e0b',
                'order' => 3,
                'settings' => [
                    'autoAdvanceAfterDays' => null,
                    'priority' => 'medium'
                ]
            ],
            [
                'name' => 'Aguardando Cliente',
                'description' => 'Aguardando resposta ou ação do cliente',
                'color' => '#ef4444',
                'order' => 4,
                'settings' => [
                    'autoAdvanceAfterDays' => 3,
                    'priority' => 'low'
                ]
            ],
            [
                'name' => 'Resolvido',
                'description' => 'Solicitação resolvida com sucesso',
                'color' => '#10b981',
                'order' => 5,
                'settings' => [
                    'autoAdvanceAfterDays' => null,
                    'priority' => 'low'
                ]
            ]
        ];

        foreach ($supportStages as $stageData) {
            Stage::create(array_merge($stageData, [
                'funnel_id' => $supportFunnel->id,
                'isActive' => true
            ]));
        }

        $this->command->info('✅ Funnels e Stages criados com sucesso!');
        $this->command->info("📊 Criados {$salesFunnel->stages()->count()} stages para o Funil de Vendas");
        $this->command->info("🔧 Criados {$maintenanceFunnel->stages()->count()} stages para o Funil de Manutenção");
        $this->command->info("🎧 Criados {$supportFunnel->stages()->count()} stages para o Funil de Suporte");
    }
}