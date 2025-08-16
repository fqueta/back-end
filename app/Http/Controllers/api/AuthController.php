<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validação
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. Credenciais
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'ativo' => 's',
            'excluido' => 'n',
        ];

        // 3. Tentativa de login
        // if (!Auth::attempt($credentials)) {
        //     return response()->json([
        //         'status' => 403,
        //         'message' => 'Sem Autorização',
        //     ], 403);
        // }

        // // 4. Usuário logado
        // $user = Auth::user();

        // // 5. Gerar token
        // $token = $user->createToken('developer')->plainTextToken;
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Gera token (Laravel Sanctum)
            $token = $user->createToken('developer')->plainTextToken;

            // Carrega menus permitidos
            $menus = $user->menusPermitidosFiltrados();

            return response()->json([
                'token'       => $token,
                'user'        => $user,
                'permissions' => [$user->permission_id],
                'menu'        => $menus
            ], 200);
        }
        // 6. Resposta
        // return response()->json([
        //     'status' => 200,
        //     'user' => $user,
        //     'token' => $token,
        //     'message' => 'Authorized',

        // ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Logout realizado com sucesso',
        ]);
    }
}
