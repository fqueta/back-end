<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuPermission;
use App\Models\Permission;
use App\Services\PermissionService;
use App\Services\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    protected PermissionService $permissionService;
    public $routeName;
    public $sec;
    public function __construct(PermissionService $permissionService)
    {
        $this->routeName = request()->route()->getName();
        $this->permissionService = $permissionService;
        $this->sec = request()->segment(3);
    }
    /**
     * Listar todas as permissões
     */
    public function index()
    {
        $user = Auth::user();
        if(!$user){
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $permission_id = $user->permission_id ?? null;
        // dd($parmission_id);
        if (! $this->permissionService->can($user, 'settings.'.$this->sec.'.view', 'view')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        return response()->json(Permission::all()->where('id','>=',$permission_id)->where('excluido','n')->where('deletado','n'), 200);
    }

    /**
     * Criar uma nova permissão
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (! $this->permissionService->can($user, 'settings.'.$this->sec.'.view', 'create')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:125|unique:permissions,name',
            'id_menu'        => 'nullable|array',
            'redirect_login' => 'nullable|string|max:255',
            'config'         => 'nullable|array',
            'description'    => 'nullable|string',
            'guard_name'     => 'nullable|string|max:125',
            // 'active'         => 'required|in:s,n',
            'autor'          => 'nullable|integer',
            // 'excluido'       => 'in:s,n',
            // 'deletado'       => 'in:s,n',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exec'    => false,
                'message' => 'Erro de validação',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $data['autor'] = Auth::id();
        // Gera token único automático, se não vier do request
        $data['token'] = $data['token'] ?? Qlib::token();
        $data['excluido'] = isset($data['excluido']) ? $data['excluido'] : 'n';
        $data['deletado'] = isset($data['deletado']) ? $data['deletado'] : 'n';
        $data['reg_excluido'] = $data['reg_excluido'] ?? null;
        $data['reg_deletado'] = $data['reg_deletado'] ?? null;
        $permission = Permission::create($data);

        return response()->json([
            'exec'    => true,
            'message' => 'Permissão criada com sucesso',
            'data'    => $permission
        ], 201);
    }

    /**
     * Mostrar uma permissão específica
     */
    public function show($id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json(['message' => 'Permissão não encontrada'], 404);
        }

        return response()->json($permission, 200);
    }

    /**
     * Atualizar uma permissão
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json(['message' => 'Permissão não encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'           => 'sometimes|required|string|max:125|unique:permissions,name,' . $id,
            'id_menu'        => 'nullable|array',
            'redirect_login' => 'nullable|string|max:255',
            'config'         => 'nullable|array',
            'description'    => 'nullable|string',
            'guard_name'     => 'nullable|string|max:125',
            // 'active'         => 'in:s,n',
            'autor'          => 'nullable|integer',
            'excluido'       => 'in:s,n',
            'deletado'       => 'in:s,n',
            'permissions'    => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $permission->update($validator->validated());

            if ($request->has('permissions')) {
                // 🔑 remove permissões antigas antes de recriar
                MenuPermission::where('permission_id', $permission->id)->delete();

                foreach ($request->permissions as $perm) {
                    MenuPermission::updateOrCreate(
                        [
                            'menu_id'       => $perm['menu_id'],
                            'permission_id' => $permission->id,
                        ],
                        [
                            'permission_key' => $perm['permission_key'],
                            'can_view'       => $perm['can_view'] ?? false,
                            'can_create'     => $perm['can_create'] ?? false,
                            'can_edit'       => $perm['can_edit'] ?? false,
                            'can_delete'     => $perm['can_delete'] ?? false,
                            'can_upload'     => $perm['can_upload'] ?? false,
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Permissão atualizada com sucesso',
                'data'    => $permission->load('menuPermissions')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao atualizar permissão',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Deletar (soft delete) uma permissão
     */
    public function destroy($id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json(['message' => 'Permissão não encontrada'], 404);
        }

        // Aqui você pode decidir se realmente deleta ou só marca como deletado
        $permission->update([
            'excluido'     => 's',
            'deletado'     => 's',
            'reg_deletado' => now()->toDateTimeString()
        ]);

        return response()->json([
            'message' => 'Permissão marcada como deletada com sucesso'
        ], 200);
    }
}
