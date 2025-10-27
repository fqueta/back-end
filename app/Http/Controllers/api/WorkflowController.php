<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * Controller para gerenciar workflows
 */
class WorkflowController extends Controller
{
    /**
     * Mapeia campos do frontend para o backend
     * Converte funnelId para funnel_id
     */
    private function map_campos(array $dados): array
    {
        if (isset($dados['funnelId'])) {
            $dados['funnel_id'] = $dados['funnelId'];
            unset($dados['funnelId']);
        }

        return $dados;
    }

    /**
     * Lista todos os workflows com filtros e paginação
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Workflow::with('funnel');

            // Filtro por funil
            if ($request->has('funnel_id')) {
                $query->where('funnel_id', $request->funnel_id);
            }

            // Filtro por status ativo
            if ($request->has('isActive')) {
                $query->where('isActive', $request->boolean('isActive'));
            }

            // Busca por nome
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $workflows = $query->paginate($perPage);

            return response()->json($workflows);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar workflows',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cria um novo workflow
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validação dos dados
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'funnel_id' => 'nullable|exists:funnels,id',
                'funnelId' => 'nullable|exists:funnels,id',
                'isActive' => 'nullable|boolean',
                'settings' => 'nullable|array',
                'settings.autoAdvance' => 'nullable|boolean',
                'settings.requireApproval' => 'nullable|boolean',
                'settings.notificationEnabled' => 'nullable|boolean',
                'settings.dueDate' => 'nullable|string',
                'settings.assignedUsers' => 'nullable|array',
                'settings.assignedUsers.*' => 'string',
            ]);

            // Aplicar mapeamento de campos
            $validated = $this->map_campos($validated);

            // Definir valores padrão
            $validated['isActive'] = $validated['isActive'] ?? true;

            // Criar o workflow
            $workflow = Workflow::create($validated);

            // Carregar relacionamentos
            $workflow->load('funnel');

            return response()->json([
                'message' => 'Workflow criado com sucesso',
                'data' => $workflow
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibe um workflow específico
     */
    public function show(string $id): JsonResponse
    {
        try {
            $workflow = Workflow::with('funnel')->findOrFail($id);

            return response()->json([
                'data' => $workflow
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Workflow não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza um workflow existente
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $workflow = Workflow::findOrFail($id);

            // Validação dos dados
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'funnel_id' => 'nullable|exists:funnels,id',
                'funnelId' => 'nullable|exists:funnels,id',
                'isActive' => 'nullable|boolean',
                'settings' => 'nullable|array',
                'settings.autoAdvance' => 'nullable|boolean',
                'settings.requireApproval' => 'nullable|boolean',
                'settings.notificationEnabled' => 'nullable|boolean',
                'settings.dueDate' => 'nullable|string',
                'settings.assignedUsers' => 'nullable|array',
                'settings.assignedUsers.*' => 'string',
            ]);

            // Aplicar mapeamento de campos
            $validated = $this->map_campos($validated);

            // Atualizar o workflow
            $workflow->update($validated);

            // Carregar relacionamentos
            $workflow->load('funnel');

            return response()->json([
                'message' => 'Workflow atualizado com sucesso',
                'data' => $workflow
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Workflow não encontrado'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove um workflow
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $workflow = Workflow::findOrFail($id);
            $workflow->delete();

            return response()->json([
                'message' => 'Workflow removido com sucesso'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Workflow não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao remover workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alterna o status ativo/inativo de um workflow
     */
    public function toggleActive(string $id): JsonResponse
    {
        try {
            $workflow = Workflow::findOrFail($id);
            $workflow->isActive = !$workflow->isActive;
            $workflow->save();

            return response()->json([
                'message' => 'Status do workflow alterado com sucesso',
                'data' => $workflow
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Workflow não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao alterar status do workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
