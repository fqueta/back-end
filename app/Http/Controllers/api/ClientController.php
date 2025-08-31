<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\PermissionService;
use App\Services\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function PHPUnit\Framework\isArray;

class ClientController extends Controller
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
     * Listar todos os clientes
     */
    public function index(Request $request)
    {
        $user = request()->user();
        if (!$user) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (!$this->permissionService->isHasPermission('view')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $perPage = $request->input('per_page', 10);
        $order_by = $request->input('order_by', 'created_at');
        $order = $request->input('order', 'desc');

        $query = Client::query()->orderBy($order_by, $order);

        // Não exibir registros marcados como deletados ou excluídos
        $query->where(function($q) {
            $q->whereNull('deletado')->orWhere('deletado', '!=', 's');
        });
        $query->where(function($q) {
            $q->whereNull('excluido')->orWhere('excluido', '!=', 's');
        });

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }
        if ($request->filled('cpf')) {
            $query->where('cpf', 'like', '%' . $request->input('cpf') . '%');
        }
        if ($request->filled('cnpj')) {
            $query->where('cnpj', 'like', '%' . $request->input('cnpj') . '%');
        }

        $clients = $query->paginate($perPage);

        // Converter config para array em cada cliente
        $clients->getCollection()->transform(function ($client) {
            if (is_string($client->config)) {
                $configArr = json_decode($client->config, true) ?? [];
                array_walk($configArr, function (&$value) {
                    if (is_null($value)) {
                        $value = (string)'';
                    }
                });
                $client->config = $configArr;
            }
            return $client;
        });

        return response()->json($clients);
    }

    /**
     * Sanitiza os dados de entrada
     */
    private function sanitizeInput($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->sanitizeInput($value);
                } elseif (is_string($value)) {
                    $data[$key] = trim($value);
                }
            }
        } elseif (is_string($data)) {
            $data = trim($data);
        }
        return $data;
    }

    /**
     * Criar um novo cliente
     */
    public function store(Request $request)
    {
        $user = request()->user();
        if (!$user) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (!$this->permissionService->isHasPermission('create')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        // Verificar se o email já existe na lixeira
        if ($request->filled('email')) {
            $existingUser = Client::withoutGlobalScope('client')
                ->where('email', $request->email)
                ->where(function($q) {
                    $q->where('deletado', 's')->orWhere('excluido', 's');
                })
                ->first();

            if ($existingUser) {
                return response()->json([
                    'message' => 'Este cadastro já está em nossa base de dados, verifique na lixeira.',
                    'errors'  => ['email' => ['Cadastro com este e-mail está na lixeira']],
                ], 422);
            }
        }

        $validator = Validator::make($request->all(), [
            'tipo_pessoa'   => ['required', Rule::in(['pf','pj'])],
            'name'          => 'required|string|max:255',
            'razao'         => 'nullable|string|max:255',
            'cpf'           => 'nullable|string|max:20|unique:users,cpf',
            'cnpj'          => 'nullable|string|max:20|unique:users,cnpj',
            'email'         => 'nullable|email|unique:users,email',
            'password'      => 'nullable|string|min:6',
            'genero'        => ['required', Rule::in(['ni','m','f'])],
            'config'        => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Validação extra de CPF
        if (!empty($request->cpf) && !Qlib::validaCpf($request->cpf)) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors'  => ['cpf' => ['CPF inválido']],
            ], 422);
        }

        $validated = $validator->validated();
        // Sanitização dos dados
        $validated = $this->sanitizeInput($validated);
        $validated['token'] = Qlib::token();
        if(isset($validated['password'])){
            $validated['password'] = Hash::make($validated['password']);
        }
        $validated['ativo'] = isset($validated['ativo']) ? $validated['ativo'] : 's';
        $validated['status'] = isset($validated['status']) ? $validated['status'] : 'actived';
        $validated['tipo_pessoa'] = isset($validated['tipo_pessoa']) ? $validated['tipo_pessoa'] : 'pf';
        $validated['permission_id'] = 5; // Força sempre grupo cliente
        $validated['config'] = isset($validated['config']) ? $this->sanitizeInput($validated['config']) : [];

        if(isArray($validated['config'])){
            $validated['config'] = json_encode($validated['config']);
        }

        $client = Client::create($validated);
        $ret['data'] = $client;
        $ret['message'] = 'Cliente criado com sucesso';
        $ret['status'] = 201;

        return response()->json($ret, 201);
    }

    /**
     * Exibir um cliente específico
     */
    public function show(Request $request, string $id)
    {
        $user = request()->user();
        if (!$user) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (!$this->permissionService->isHasPermission('view')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $client = Client::findOrFail($id);

        // Converter config para array
        if (is_string($client->config)) {
            $client->config = json_decode($client->config, true) ?? [];
        }

        return response()->json($client);
    }

    /**
     * Retorna dados do cliente
     */
    public function can_access(Request $request)
    {
        $user = $request->user();
        if(!$user){
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        return response()->json($user);
    }

    /**
     * Atualizar um cliente específico
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (!$this->permissionService->isHasPermission('edit')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $clientToUpdate = Client::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tipo_pessoa'   => ['sometimes', Rule::in(['pf','pj'])],
            'name'          => 'sometimes|required|string|max:255',
            'razao'         => 'nullable|string|max:255',
            'cpf'           => ['nullable','string','max:20', Rule::unique('users','cpf')->ignore($clientToUpdate->id)],
            'cnpj'          => ['nullable','string','max:20', Rule::unique('users','cnpj')->ignore($clientToUpdate->id)],
            'email'         => ['nullable','email', Rule::unique('users','email')->ignore($clientToUpdate->id)],
            'password'      => 'nullable|string|min:6',
            'genero'        => ['sometimes', Rule::in(['ni','m','f'])],
            'verificado'    => ['sometimes', Rule::in(['n','s'])],
            'config'        => 'array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exec'=>false,
                'message' => 'Erro de validação',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Validação extra de CPF
        if (!empty($request->cpf) && !Qlib::validaCpf($request->cpf)) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors'  => ['cpf' => ['CPF inválido']],
            ], 422);
        }

        $validated = $validator->validated();

        // Sanitização dos dados
        $validated = $this->sanitizeInput($validated);

        // Tratar senha se fornecida
        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Garantir que permission_id seja sempre 5 (cliente)
        $validated['permission_id'] = 5;

        // Tratar config se fornecido
        if (isset($validated['config'])) {
            $validated['config'] = $this->sanitizeInput($validated['config']);
            if (isArray($validated['config'])) {
                $validated['config'] = json_encode($validated['config']);
            }
        }

        $clientToUpdate->update($validated);

        // Converter config para array na resposta
        if (is_string($clientToUpdate->config)) {
            $clientToUpdate->config = json_decode($clientToUpdate->config, true) ?? [];
        }

        $ret['data'] = $clientToUpdate;
        $ret['message'] = 'Cliente atualizado com sucesso';
        $ret['status'] = 200;

        return response()->json($ret);
    }

    /**
     * Mover cliente para a lixeira
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (!$this->permissionService->isHasPermission('delete')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $client = Client::findOrFail($id);

        // Mover para lixeira em vez de excluir permanentemente
        $client->update([
            'deletado' => 's',
            'reg_deletado' => json_encode([
                'usuario' => $user->id,
                'nome' => $user->name,
                'created_at' => now(),
            ])
        ]);

        return response()->json([
            'message' => 'Cliente movido para a lixeira com sucesso',
            'status' => 200
        ]);
    }

    /**
     * Listar clientes na lixeira
     */
    public function trash(Request $request)
    {
        $user = request()->user();
        if (!$user) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (!$this->permissionService->isHasPermission('view')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $perPage = $request->input('per_page', 10);
        $order_by = $request->input('order_by', 'created_at');
        $order = $request->input('order', 'desc');

        $query = Client::withoutGlobalScope('client')
            ->where('permission_id', 5)
            ->where('deletado', 's')
            ->orderBy($order_by, $order);

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }
        if ($request->filled('cpf')) {
            $query->where('cpf', 'like', '%' . $request->input('cpf') . '%');
        }
        if ($request->filled('cnpj')) {
            $query->where('cnpj', 'like', '%' . $request->input('cnpj') . '%');
        }

        $clients = $query->paginate($perPage);

        // Converter config para array em cada cliente
        $clients->getCollection()->transform(function ($client) {
            if (is_string($client->config)) {
                $configArr = json_decode($client->config, true) ?? [];
                array_walk($configArr, function (&$value) {
                    if (is_null($value)) {
                        $value = (string)'';
                    }
                });
                $client->config = $configArr;
            }
            return $client;
        });

        return response()->json($clients);
    }

    /**
     * Restaurar cliente da lixeira
     */
    public function restore(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (!$this->permissionService->isHasPermission('delete')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $client = Client::withoutGlobalScope('client')
            ->where('id', $id)
            ->where('deletado', 's')
            ->where('permission_id', 5)
            ->firstOrFail();

        $client->update([
            'deletado' => 'n',
            'reg_deletado' => null
        ]);

        return response()->json([
            'message' => 'Cliente restaurado com sucesso',
            'status' => 200
        ]);
    }

    /**
     * Excluir cliente permanentemente
     */
    public function forceDelete(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (!$this->permissionService->isHasPermission('delete')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $client = Client::withoutGlobalScope('client')
            ->where('id', $id)
            ->where('permission_id', 5)
            ->firstOrFail();

        $client->delete();

        return response()->json([
            'message' => 'Cliente excluído permanentemente',
            'status' => 200
        ]);
    }
}
