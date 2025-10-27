<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para gerenciar as etapas (stages) dos funis de atendimento
 */
class Stage extends Model
{
    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'name',
        'description',
        'color',
        'funnel_id',
        'isActive',
        'order',
        'settings'
    ];

    /**
     * Conversões de tipos para os atributos
     */
    protected $casts = [
        'isActive' => 'boolean',
        'settings' => 'array',
        'order' => 'integer',
        'funnel_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento: Stage pertence a um Funnel
     */
    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }

    /**
     * Scope para buscar apenas stages ativos
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', true);
    }

    /**
     * Scope para buscar apenas stages inativos
     */
    public function scopeInactive($query)
    {
        return $query->where('isActive', false);
    }

    /**
     * Scope para ordenar por ordem
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope para buscar por funil
     */
    public function scopeByFunnel($query, $funnelId)
    {
        return $query->where('funnel_id', $funnelId);
    }

    /**
     * Retorna as configurações padrão para um stage
     */
    public static function getDefaultSettings(): array
    {
        return [
            'autoAdvance' => false,
            'maxItems' => null,
            'notifyOnEntry' => false,
            'notifyOnExit' => false,
            'requireApproval' => false,
            'timeLimit' => null
        ];
    }

    /**
     * Retorna as configurações mescladas com os padrões
     */
    public function getSettingsWithDefaults(): array
    {
        $defaultSettings = self::getDefaultSettings();
        $currentSettings = $this->settings ?? [];
        
        return array_merge($defaultSettings, $currentSettings);
    }

    /**
     * Verifica se o stage tem limite de tempo
     */
    public function hasTimeLimit(): bool
    {
        $settings = $this->getSettingsWithDefaults();
        return !empty($settings['timeLimit']) && $settings['timeLimit'] > 0;
    }

    /**
     * Verifica se o stage tem limite máximo de itens
     */
    public function hasMaxItems(): bool
    {
        $settings = $this->getSettingsWithDefaults();
        return !empty($settings['maxItems']) && $settings['maxItems'] > 0;
    }
}
