<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\AttendanceStatus;

/**
 * Modelo para atendimentos de aeronaves
 */
class AircraftAttendance extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'aircraft_attendances';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // Campos obrigatórios
        'aircraft_id',
        'service_order_id',
        'title',
        'status',
        'priority',
        'started_at',
        'client_id',
        'client_name',
        
        // Campos opcionais
        'description',
        'current_funnel_id',
        'current_funnel_name',
        'current_stage_id',
        'current_stage_name',
        'completed_at',
        'estimated_completion',
        'assigned_to',
        'assigned_to_name',
        'notes',
        'internal_notes',
        'created_by',
        'updated_by',
        
        // Campos calculados/resumos
        'total_duration_minutes',
        'stages_count',
        'events_count',
        'service_summary',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => AttendanceStatus::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_completion' => 'datetime',
        'service_summary' => 'array',
        'aircraft_id' => 'integer',
        'service_order_id' => 'integer',
        'client_id' => 'integer',
        'current_funnel_id' => 'integer',
        'current_stage_id' => 'integer',
        'assigned_to' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'total_duration_minutes' => 'integer',
        'stages_count' => 'integer',
        'events_count' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'internal_notes',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'status_label',
        'status_color',
        'is_active',
        'is_finished',
        'duration_formatted',
    ];

    /**
     * Relacionamento com a aeronave
     */
    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(Aircraft::class, 'aircraft_id', 'ID');
    }

    /**
     * Relacionamento com a ordem de serviço
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    /**
     * Relacionamento com o cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Relacionamento com o responsável
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Relacionamento com o criador
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relacionamento com o último editor
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relacionamento com a etapa atual
     */
    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'current_stage_id');
    }

    /**
     * Relacionamento com eventos de rastreamento
     */
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'object_id')
                    ->where('object_type', 'aircraft_attendance');
    }

    /**
     * Accessor para o label do status
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * Accessor para a cor do status
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status->color();
    }

    /**
     * Accessor para verificar se está ativo
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status->isActive();
    }

    /**
     * Accessor para verificar se está finalizado
     */
    public function getIsFinishedAttribute(): bool
    {
        return $this->status->isFinished();
    }

    /**
     * Accessor para duração formatada
     */
    public function getDurationFormattedAttribute(): ?string
    {
        if (!$this->total_duration_minutes) {
            return null;
        }

        $hours = intval($this->total_duration_minutes / 60);
        $minutes = $this->total_duration_minutes % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        }

        return sprintf('%dm', $minutes);
    }

    /**
     * Scope para filtrar por status
     */
    public function scopeByStatus($query, AttendanceStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por prioridade
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope para filtrar por aeronave
     */
    public function scopeByAircraft($query, int $aircraftId)
    {
        return $query->where('aircraft_id', $aircraftId);
    }

    /**
     * Scope para filtrar por cliente
     */
    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope para filtrar por responsável
     */
    public function scopeByAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope para atendimentos ativos
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', AttendanceStatus::active());
    }

    /**
     * Scope para atendimentos finalizados
     */
    public function scopeFinished($query)
    {
        return $query->whereIn('status', AttendanceStatus::finished());
    }

    /**
     * Scope para ordenar por prioridade
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')");
    }

    /**
     * Atualiza os contadores de etapas e eventos
     */
    public function updateCounters(): void
    {
        $this->update([
            'events_count' => $this->trackingEvents()->count(),
        ]);
    }

    /**
     * Calcula e atualiza a duração total
     */
    public function calculateDuration(): void
    {
        if ($this->started_at && $this->completed_at) {
            $duration = $this->started_at->diffInMinutes($this->completed_at);
            $this->update(['total_duration_minutes' => $duration]);
        }
    }

    /**
     * Marca o atendimento como concluído
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => AttendanceStatus::COMPLETED,
            'completed_at' => now(),
        ]);
        
        $this->calculateDuration();
    }

    /**
     * Marca o atendimento como cancelado
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => AttendanceStatus::CANCELLED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Inicia o atendimento
     */
    public function start(): void
    {
        $this->update([
            'status' => AttendanceStatus::IN_PROGRESS,
            'started_at' => now(),
        ]);
    }

    /**
     * Coloca o atendimento em espera
     */
    public function putOnHold(): void
    {
        $this->update([
            'status' => AttendanceStatus::ON_HOLD,
        ]);
    }
}