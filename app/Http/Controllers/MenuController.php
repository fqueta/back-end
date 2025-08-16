<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class MenuController extends Controller
{
    public function getMenus()
    {
        $user = Auth::user();

        return response()->json([
            'menus' => $user->menusPermitidosFiltrados()
        ]);
    }
}
