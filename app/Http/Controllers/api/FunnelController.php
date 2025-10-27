<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Funnel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

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
}
