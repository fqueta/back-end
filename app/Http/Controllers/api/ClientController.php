<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index(Request $request)
    {
        if (! $this->permissionService->can($request->user(), 'clients.view', 'view')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        return response()->json(Client::all());
    }

    public function store(Request $request)
    {
        // dd($request->all());
        if (! $this->permissionService->can($request->user(), 'clients.view', 'create')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string|max:20',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $client = Client::create($validated);

        return response()->json($client, 201);
    }

    public function show(Request $request, $id)
    {
        if (! $this->permissionService->can($request->user(), 'clients.view', 'view')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $client = Client::findOrFail($id);

        return response()->json($client);
    }

    public function update(Request $request, $id)
    {
        if (! $this->permissionService->can($request->user(), 'clients.view', 'edit')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'phone'    => 'nullable|string|max:20',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $client->update($validated);

        return response()->json($client);
    }

    public function destroy(Request $request, $id)
    {
        if (! $this->permissionService->can($request->user(), 'clients.view', 'delete')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $client = Client::findOrFail($id);
        $client->delete();

        return response()->json(['message' => 'Cliente removido com sucesso']);
    }
}
