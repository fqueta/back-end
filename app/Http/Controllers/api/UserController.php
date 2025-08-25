<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PermissionService;
use App\Services\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function PHPUnit\Framework\isArray;

class UserController extends Controller
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
     * Metodo para veriricar se o usuario tem permissão para executar ao acessar esse recurso atraves de ''
     * @params string 'view | create | edit | delete'
     */
    // private function isHasPermission($permissao=''){
    //     $user = request()->user();
    //     if ($this->permissionService->can($user, $this->routeName, $permissao)) {
    //         return true;
    //     }else{
    //         return false;
    //     }
    // }
    /**
     * Listar todos os usuários
     */
    public function index(Request $request)
    {
        $users = User::paginate(10);
        return response()->json($users);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $d = $request->all();
        $user = $request->user();
        if(!$user){
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        // if (! $this->permissionService->can($user, 'settings.'.$this->sec.'.view', 'create')) {
        //     return response()->json(['error' => 'Acesso negado'], 403);
        // }
        // $permission_id = $user->permission_id ?? null;
        if (!$this->permissionService->isHasPermission('create')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $validator = Validator::make($request->all(), [
            'tipo_pessoa'   => ['required', Rule::in(['pf','pj'])],
            'name'          => 'required|string|max:255',
            'razao'         => 'nullable|string|max:255',
            'cpf'           => 'nullable|string|max:20|unique:users,cpf',
            'cnpj'          => 'nullable|string|max:20|unique:users,cnpj',
            'email'         => 'nullable|email|unique:users,email',
            'password'      => 'required|string|min:6',
            'status'        => ['required', Rule::in(['actived','inactived','pre_registred'])],
            'genero'        => ['required', Rule::in(['ni','m','f'])],
            'verificado'    => ['required', Rule::in(['n','s'])],
            'permission_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        // dd($validated);
        $validated['token'] = Qlib::token();
        $validated['password'] = Hash::make($validated['password']);
        $validated['ativo'] = isset($validated['ativo']) ? $validated['ativo'] : 's';
        $validated['status'] = isset($validated['status']) ? $validated['status'] : 'actived';
        $validated['tipo_pessoa'] = isset($validated['tipo_pessoa']) ? $validated['tipo_pessoa'] : 'pf';
        $validated['permission_id'] = isset($validated['permission_id']) ? $validated['permission_id'] : 5;
        $validated['config'] = isset($validated['config']) ? $validated['config'] : json_encode([]);
        if(isArray($validated['config'])){
            $validated['config'] = json_encode($validated['config']);
        }
        $user = User::create($validated);
        $ret['data'] = $user;
        $ret['message'] = 'Usuário criado com sucesso';
        $ret['status'] = 201;
        return response()->json($ret, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $token)
    {
        // $d = $request->all();
        $user_d = Auth::user();
        if(!$user_d){
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (! $this->permissionService->can($user_d, 'settings.'.$this->sec.'.view', 'view')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        // if (! $this->permissionService->can($user, 'clients.view', 'view')) {
        //     return response()->json(['error' => 'Acesso negado'], 403);
        // }

        // $user = User::findOrFail($user);
        $user = User::where('token', $token)->firstOrFail();
        // $user = User::findOrFail($user);

        return response()->json($user);
    }
    /**
     * retorna dados do usuario
     */
    public function can_access(Request $request)
    {
        $user = $request->user();
        // dd($user);
        if(!$user){
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        return response()->json($user);
    }
    public function perfil(Request $request)
    {
        $user = $request->user();
        // dd($user);
        if(!$user){
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        // if (! $this->permissionService->can($user_d, 'settings.'.$this->sec.'.view', 'view')) {
        //     return response()->json(['error' => 'Acesso negado'], 403);
        // }
        // if (! $this->permissionService->can($user, 'clients.view', 'view')) {
        //     return response()->json(['error' => 'Acesso negado'], 403);
        // }
        return response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
