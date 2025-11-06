<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Funnel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\Stage;
use Illuminate\Support\Facades\DB;

/**
 * Controller for managing sales/service funnels
 */
class FunnelController extends Controller
{
    /**
     * Display a listing of funnels with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Funnel::query();

        // Filter by active status if provided
        if ($request->has('isActive')) {
            $isActive = filter_var($request->isActive, FILTER_VALIDATE_BOOLEAN);
            $query->where('isActive', $isActive);
        }

        // Search by name if provided
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Order by name by default
        $funnels = $query->orderBy('name')->get();
        $ret['data'] = $funnels;
        $ret['total'] = $funnels->count();
        return response()->json($ret);
    }

    /**
     * Store a newly created funnel in storage
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'isActive' => 'nullable|boolean',
                'settings' => 'nullable|array',
                'settings.autoAdvance' => 'nullable|boolean',
                'settings.notifyOnStageChange' => 'nullable|boolean',
                'settings.requireApproval' => 'nullable|boolean',
            ]);

            // Set default values
            $validated['color'] = $validated['color'] ?? '#3b82f6';
            $validated['isActive'] = $validated['isActive'] ?? true;

            // Merge settings with defaults
            if (isset($validated['settings'])) {
                $validated['settings'] = array_merge(
                    Funnel::getDefaultSettings(),
                    $validated['settings']
                );
            }

            $funnel = Funnel::create($validated);

            return response()->json($funnel, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados de validação inválidos',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified funnel
     */
    public function show(string $id): JsonResponse
    {
        $funnel = Funnel::find($id);

        if (!$funnel) {
            return response()->json([
                'message' => 'Funil não encontrado'
            ], 404);
        }

        return response()->json($funnel);
    }

    /**
     * Update the specified funnel in storage
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $funnel = Funnel::find($id);

        if (!$funnel) {
            return response()->json([
                'message' => 'Funil não encontrado'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'isActive' => 'nullable|boolean',
                'settings' => 'nullable|array',
                'settings.autoAdvance' => 'nullable|boolean',
                'settings.notifyOnStageChange' => 'nullable|boolean',
                'settings.requireApproval' => 'nullable|boolean',
            ]);

            // Merge settings with existing ones if provided
            if (isset($validated['settings'])) {
                $currentSettings = $funnel->getSettingsWithDefaults();
                $validated['settings'] = array_merge($currentSettings, $validated['settings']);
            }

            $funnel->update($validated);

            return response()->json($funnel);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados de validação inválidos',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified funnel from storage
     */
    public function destroy(string $id): JsonResponse
    {
        $funnel = Funnel::find($id);

        if (!$funnel) {
            return response()->json([
                'message' => 'Funil não encontrado'
            ], 404);
        }

        $funnel->delete();

        return response()->json([
            'message' => 'Funil excluído com sucesso'
        ]);
    }

    /**
     * Toggle the active status of a funnel
     */
    public function toggleActive(string $id): JsonResponse
    {
        $funnel = Funnel::find($id);

        if (!$funnel) {
            return response()->json([
                'message' => 'Funil não encontrado'
            ], 404);
        }

        $funnel->update(['isActive' => !$funnel->isActive]);

        return response()->json([
            'message' => 'Status do funil atualizado com sucesso',
            'funnel' => $funnel
        ]);
    }

    /**
     * Get all stages for a specific funnel
     */
    public function stages(string $id): JsonResponse
    {
        $funnel = Funnel::find($id);

        if (!$funnel) {
            return response()->json([
                'data' => [],
                'message' => 'Funil não encontrado',
                'success' => false
            ], 404);
        }

        // Get stages ordered by order field
        $stages = $funnel->stages()->orderBy('order')->get();

        // Transform data to match the required structure
        $transformedStages = $stages->map(function ($stage) {
            return [
                'id' => (string) $stage->id,
                'name' => $stage->name,
                'funnelId' => (string) $stage->funnel_id,
                'order' => $stage->order,
                'color' => $stage->color,
                'description' => $stage->description,
                'isActive' => $stage->isActive,
                'createdAt' => $stage->created_at ? $stage->created_at->toISOString() : null,
                'updatedAt' => $stage->updated_at ? $stage->updated_at->toISOString() : null,
            ];
        });

        return response()->json([
            'data' => $transformedStages,
            'message' => 'Etapas do funil recuperadas com sucesso',
            'success' => true
        ]);
    }

    /**
     * Reordenar etapas de um funil com base em uma lista de IDs.
     *
     * Recebe um body no formato:
     * {
     *   "stageIds": ["6", "8", "7", "9", "10", "11"]
     * }
     *
     * - Valida se o funil existe.
     * - Valida se os IDs fornecidos pertencem ao funil.
     * - Atualiza a coluna `order` sequencialmente conforme a ordem dos IDs.
     * - Mantém as etapas não informadas ao final, preservando a ordem relativa atual.
     */
    public function reorderStages(Request $request, string $id): JsonResponse
    {
        try {
            $funnel = Funnel::find($id);
            if (!$funnel) {
                return response()->json([
                    'message' => 'Funil não encontrado'
                ], 404);
            }

            $validated = $request->validate([
                'stageIds' => 'required|array|min:1',
                'stageIds.*' => 'required|integer|distinct'
            ], [
                'stageIds.required' => 'A lista de etapas é obrigatória',
                'stageIds.array' => 'A lista de etapas deve ser um array',
                'stageIds.min' => 'Informe pelo menos uma etapa',
                'stageIds.*.required' => 'Cada etapa deve ter um ID',
                'stageIds.*.integer' => 'Os IDs das etapas devem ser inteiros',
                'stageIds.*.distinct' => 'A lista de etapas contém IDs duplicados'
            ]);

            // Buscar todas as etapas do funil
            $allStages = Stage::where('funnel_id', $funnel->id)->orderBy('order')->get();
            $allStageIds = $allStages->pluck('id')->all();

            // Validar se todos os IDs fornecidos pertencem ao funil
            $invalidIds = array_diff($validated['stageIds'], $allStageIds);
            if (!empty($invalidIds)) {
                return response()->json([
                    'message' => 'Uma ou mais etapas não pertencem ao funil',
                    'invalidIds' => array_values($invalidIds)
                ], 422);
            }

            DB::transaction(function () use ($validated, $funnel, $allStages) {
                // Atualizar ordem para os IDs fornecidos
                foreach ($validated['stageIds'] as $index => $stageId) {
                    Stage::where('id', $stageId)
                        ->where('funnel_id', $funnel->id)
                        ->update(['order' => $index + 1]);
                }

                // Etapas restantes (não informadas) mantêm a ordem relativa após as informadas
                $remaining = $allStages->whereNotIn('id', $validated['stageIds'])->values();
                $base = count($validated['stageIds']);
                foreach ($remaining as $offset => $stage) {
                    Stage::where('id', $stage->id)
                        ->where('funnel_id', $funnel->id)
                        ->update(['order' => $base + $offset + 1]);
                }
            });

            // Retornar lista atualizada
            $updatedStages = Stage::where('funnel_id', $funnel->id)
                ->orderBy('order')
                ->get()
                ->map(function ($stage) {
                    return [
                        'id' => (string) $stage->id,
                        'name' => $stage->name,
                        'funnelId' => (string) $stage->funnel_id,
                        'order' => $stage->order,
                        'color' => $stage->color,
                        'description' => $stage->description,
                        'isActive' => $stage->isActive,
                        'createdAt' => $stage->created_at ? $stage->created_at->toISOString() : null,
                        'updatedAt' => $stage->updated_at ? $stage->updated_at->toISOString() : null,
                    ];
                });

            return response()->json([
                'data' => $updatedStages,
                'message' => 'Etapas reordenadas com sucesso',
                'success' => true
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Dados de validação inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao reordenar etapas',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
