<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Models\Funnel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Controller para gerenciar as etapas (stages) dos funis de atendimento
 */
class StageController extends Controller
{
    /**
     * Listar todas as etapas com filtros opcionais
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Stage::with('funnel');

            // Filtro por funil
            if ($request->has('funnelId') && $request->funnelId !== '') {
                $query->where('funnel_id', $request->funnelId);
            }

            // Filtro por status ativo
            if ($request->has('isActive') && $request->isActive !== '') {
                $isActive = filter_var($request->isActive, FILTER_VALIDATE_BOOLEAN);
                $query->where('isActive', $isActive);
            }

            // Busca por nome ou descrição
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Ordenação
            $query->orderBy('funnel_id')->orderBy('order');

            $stages = $query->get();

            // Aplicar compatibilidade com front-end
            $stages = $stages->map(function ($stage) {
                return $this->compatibilidade($stage->toArray());
            });
            return response()->json([
                'data' => $stages,
                'total' => $stages->count(),
                'message' => 'Etapas listadas com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao listar etapas',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Metodo para compatibilidade com o front-end
     */
    public function compatibilidade($dados)
    {
        $dados['createdAt'] = $dados['created_at'] ?? null;
        $dados['updatedAt'] = $dados['updated_at'] ?? null;
        $dados['funnelId'] = $dados['funnel_id'] ?? null;
        return $dados;
    }
    /**
     * Metodos para mapeamento de campos para compatibilidade com o front-end
     */
    public function map_campos($dados)
    {
        if (isset($dados['funnelId'])) {
            $dados['funnel_id'] = $dados['funnelId'];
            unset($dados['funnelId']);
        }
        return $dados;
    }
    /**
     * Criar uma nova etapa
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'funnel_id' => 'required_without:funnelId|integer|exists:funnels,id',
                'funnelId' => 'required_without:funnel_id|integer|exists:funnels,id',
                'isActive' => 'nullable|boolean',
                'order' => 'nullable|integer|min:1',
                'settings' => 'nullable|array',
                'settings.autoAdvance' => 'nullable|boolean',
                'settings.maxItems' => 'nullable|integer|min:1',
                'settings.notifyOnEntry' => 'nullable|boolean',
                'settings.notifyOnExit' => 'nullable|boolean',
                'settings.requireApproval' => 'nullable|boolean',
                'settings.timeLimit' => 'nullable|integer|min:1'
            ], [
                'name.required' => 'O nome da etapa é obrigatório',
                'name.max' => 'O nome da etapa não pode ter mais de 255 caracteres',
                'color.size' => 'A cor deve ter exatamente 7 caracteres (ex: #3b82f6)',
                'color.regex' => 'A cor deve estar no formato hexadecimal (ex: #3b82f6)',
                'funnel_id.required' => 'O ID do funil é obrigatório',
                'funnel_id.exists' => 'O funil especificado não existe',
                'order.min' => 'A ordem deve ser no mínimo 1',
                'settings.maxItems.min' => 'O máximo de itens deve ser no mínimo 1',
                'settings.timeLimit.min' => 'O limite de tempo deve ser no mínimo 1'
            ]);

            // Aplicar mapeamento de campos
            $validated = $this->map_campos($validated);

            // Se não foi fornecida uma ordem, usar a próxima disponível para o funil
            if (!isset($validated['order'])) {
                $maxOrder = Stage::where('funnel_id', $validated['funnel_id'])->max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            // Definir valores padrão
            $validated['color'] = $validated['color'] ?? '#3b82f6';
            $validated['isActive'] = $validated['isActive'] ?? true;

            // Mesclar configurações com padrões
            if (isset($validated['settings'])) {
                $defaultSettings = Stage::getDefaultSettings();
                $validated['settings'] = array_merge($defaultSettings, $validated['settings']);
            }

            $stage = Stage::create($validated);
            $stage->load('funnel');

            return response()->json([
                'stage' => $stage,
                'message' => 'Etapa criada com sucesso'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Dados de validação inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao criar etapa',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibir uma etapa específica
     */
    public function show(string $id): JsonResponse
    {
        try {
            $stage = Stage::with('funnel')->findOrFail($id);

            return response()->json([
                'stage' => $stage,
                'message' => 'Etapa encontrada com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Etapa não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao buscar etapa',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar uma etapa existente
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $stage = Stage::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'funnel_id' => 'sometimes|required|integer|exists:funnels,id',
                'funnelId' => 'sometimes|required|integer|exists:funnels,id',
                'isActive' => 'nullable|boolean',
                'order' => 'nullable|integer|min:1',
                'settings' => 'nullable|array',
                'settings.autoAdvance' => 'nullable|boolean',
                'settings.maxItems' => 'nullable|integer|min:1',
                'settings.notifyOnEntry' => 'nullable|boolean',
                'settings.notifyOnExit' => 'nullable|boolean',
                'settings.requireApproval' => 'nullable|boolean',
                'settings.timeLimit' => 'nullable|integer|min:1'
            ], [
                'name.required' => 'O nome da etapa é obrigatório',
                'name.max' => 'O nome da etapa não pode ter mais de 255 caracteres',
                'color.size' => 'A cor deve ter exatamente 7 caracteres (ex: #3b82f6)',
                'color.regex' => 'A cor deve estar no formato hexadecimal (ex: #3b82f6)',
                'funnel_id.required' => 'O ID do funil é obrigatório',
                'funnel_id.exists' => 'O funil especificado não existe',
                'order.min' => 'A ordem deve ser no mínimo 1',
                'settings.maxItems.min' => 'O máximo de itens deve ser no mínimo 1',
                'settings.timeLimit.min' => 'O limite de tempo deve ser no mínimo 1'
            ]);

            // Aplicar mapeamento de campos
            $validated = $this->map_campos($validated);

            // Mesclar configurações existentes com as novas
            if (isset($validated['settings'])) {
                $currentSettings = $stage->getSettingsWithDefaults();
                $validated['settings'] = array_merge($currentSettings, $validated['settings']);
            }

            $stage->update($validated);
            $stage->load('funnel');

            return response()->json([
                'stage' => $stage,
                'message' => 'Etapa atualizada com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Etapa não encontrada'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Dados de validação inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao atualizar etapa',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir uma etapa
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $stage = Stage::findOrFail($id);
            $stage->delete();

            return response()->json([
                'message' => 'Etapa excluída com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Etapa não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao excluir etapa',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alternar o status ativo/inativo de uma etapa
     */
    public function toggleActive(string $id): JsonResponse
    {
        try {
            $stage = Stage::findOrFail($id);
            $stage->isActive = !$stage->isActive;
            $stage->save();

            $status = $stage->isActive ? 'ativada' : 'desativada';

            return response()->json([
                'stage' => $stage,
                'message' => "Etapa {$status} com sucesso"
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Etapa não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao alterar status da etapa',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reordenar etapas de um funil
     */
    public function reorder(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'funnel_id' => 'required|integer|exists:funnels,id',
                'stages' => 'required|array',
                'stages.*.id' => 'required|integer|exists:stages,id',
                'stages.*.order' => 'required|integer|min:1'
            ], [
                'funnel_id.required' => 'O ID do funil é obrigatório',
                'funnel_id.exists' => 'O funil especificado não existe',
                'stages.required' => 'A lista de etapas é obrigatória',
                'stages.*.id.required' => 'O ID da etapa é obrigatório',
                'stages.*.id.exists' => 'Uma das etapas especificadas não existe',
                'stages.*.order.required' => 'A ordem da etapa é obrigatória',
                'stages.*.order.min' => 'A ordem deve ser no mínimo 1'
            ]);

            foreach ($validated['stages'] as $stageData) {
                Stage::where('id', $stageData['id'])
                     ->where('funnel_id', $validated['funnel_id'])
                     ->update(['order' => $stageData['order']]);
            }

            $stages = Stage::where('funnel_id', $validated['funnel_id'])
                          ->orderBy('order')
                          ->get();

            return response()->json([
                'stages' => $stages,
                'message' => 'Etapas reordenadas com sucesso'
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
