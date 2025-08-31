<?php

declare(strict_types=1);

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ClientController;
use App\Http\Controllers\Api\DashboardMetricController;
use App\Http\Controllers\api\MenuPermissionController;
use App\Http\Controllers\api\OptionController;
use App\Http\Controllers\api\PermissionController;
use App\Http\Controllers\api\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TesteController;
use App\Services\Escola;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Api\PermissionMenuController;
use App\Http\Controllers\api\UserController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Route::get('/', function () {
    //     return Inertia::render('welcome');
    // })->name('home');
    Route::get('/teste', [ TesteController::class,'index'])->name('teste');
    // // Route::get('/', function () {
    //     //     return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    //     // });
    // // Route::middleware(['auth', 'verified'])->group(function () {
    // //     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // //     // Route::get('profile', function () {
    // //     //     return Inertia::render('profile');
    // //     // })->name('profile');
    // // });

    // require __DIR__.'/settings.php';
    // require __DIR__.'/auth.php';

});

Route::name('api.')->prefix('api/v1')->middleware([
    'api',
    // 'auth:sanctum',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::post('/login',[AuthController::class,'login'])->name('login');

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisterController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    Route::fallback(function () {
        return response()->json(['message' => 'Rota não encontrada'], 404);
    });
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user',[UserController::class,'perfil'])->name('perfil.user');
        Route::get('user/can',[UserController::class,'can_access'])->name('perfil.can');
        Route::post('/logout',[AuthController::class,'logout'])->name('logout');
        Route::apiResource('users', UserController::class,['parameters' => [
            'users' => 'id'
        ]]);
        Route::apiResource('clients', ClientController::class,['parameters' => [
            'clients' => 'id'
        ]]);
        Route::get('clients/trash', [ClientController::class, 'trash'])->name('clients.trash');
        Route::put('clients/{id}/restore', [ClientController::class, 'restore'])->name('clients.restore');
        Route::delete('clients/{id}/force', [ClientController::class, 'forceDelete'])->name('clients.forceDelete');
        
        // Rotas para options
        Route::apiResource('options', OptionController::class,['parameters' => [
            'options' => 'id'
        ]]);
        Route::get('options/trash', [OptionController::class, 'trash'])->name('options.trash');
        Route::put('options/{id}/restore', [OptionController::class, 'restore'])->name('options.restore');
        Route::delete('options/{id}/force', [OptionController::class, 'forceDelete'])->name('options.forceDelete');
        
        // Route::apiResource('clients', ClientController::class,['parameters' => [
        //     'clients' => 'id'
        // ]]);
        Route::get('users/trash', [UserController::class, 'trash'])->name('users.trash');
        Route::get('metrics/filter', [DashboardMetricController::class, 'filter']);
        Route::apiResource('metrics', DashboardMetricController::class,['parameters' => [
            'metrics' => 'id'
        ]]);
        // rota flexível de filtros
        Route::get('menus', [MenuController::class, 'getMenus']);
        Route::apiResource('permissions', PermissionController::class,['parameters' => [
            'permissions' => 'id'
        ]]);
        Route::prefix('permissions')->group(function () {
            Route::get('{id}/menu-permissions', [MenuPermissionController::class, 'show'])->name('menu-permissions.show');
            Route::put('{id}/menu-permissions', [MenuPermissionController::class, 'updatePermissions'])->name('menu-permissions.update');
            // Route::post('{id}/menus', [PermissionMenuController::class, 'update']);
        });

    });


});
