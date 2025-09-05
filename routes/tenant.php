<?php

declare(strict_types=1);

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ClientController;
use App\Http\Controllers\api\MenuPermissionController;
use App\Http\Controllers\api\OptionController;
use App\Http\Controllers\api\PermissionController;
use App\Http\Controllers\api\PostController;
use App\Http\Controllers\api\AircraftController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\MetricasController;
use App\Http\Controllers\api\ProductUnitController;
use App\Http\Controllers\api\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TesteController;
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

        // Rotas para posts
        Route::apiResource('posts', PostController::class,['parameters' => [
            'posts' => 'id'
        ]]);
        Route::get('posts/trash', [PostController::class, 'trash'])->name('posts.trash');
        Route::put('posts/{id}/restore', [PostController::class, 'restore'])->name('posts.restore');
        Route::delete('posts/{id}/force', [PostController::class, 'forceDelete'])->name('posts.forceDelete');

        // Rotas para aircraft
        Route::apiResource('aircraft', AircraftController::class,['parameters' => [
            'aircraft' => 'id'
        ]]);
        Route::get('aircraft/trash', [AircraftController::class, 'trash'])->name('aircraft.trash');
        Route::put('aircraft/{id}/restore', [AircraftController::class, 'restore'])->name('aircraft.restore');
        Route::delete('aircraft/{id}/force', [AircraftController::class, 'forceDelete'])->name('aircraft.forceDelete');

        // Rotas para categories
        Route::apiResource('categories', CategoryController::class,['parameters' => [
            'categories' => 'id'
        ]]);
        Route::get('categories/trash', [CategoryController::class, 'trash'])->name('categories.trash');
        Route::put('categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
        Route::delete('categories/{id}/force', [CategoryController::class, 'forceDelete'])->name('categories.forceDelete');
        Route::get('categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');
        Route::get('service-categories', [CategoryController::class, 'indexServiceCategories'])->name('service-categories');
        /**Rota para o cadasto de produto */
        Route::get('product-categories', [CategoryController::class, 'index'])->name('product-categories');

        // Rotas para product-units
        Route::apiResource('product-units', ProductUnitController::class,['parameters' => [
            'product-units' => 'id'
        ]]);
        Route::get('product-units/trash', [ProductUnitController::class, 'trash'])->name('product-units.trash');
        Route::put('product-units/{id}/restore', [ProductUnitController::class, 'restore'])->name('product-units.restore');
        Route::delete('product-units/{id}/force', [ProductUnitController::class, 'forceDelete'])->name('product-units.forceDelete');

        // Route::apiResource('clients', ClientController::class,['parameters' => [
        //     'clients' => 'id'
        // ]]);
        Route::get('users/trash', [UserController::class, 'trash'])->name('users.trash');
        Route::get('metrics/filter', [MetricasController::class, 'filter']);
        Route::apiResource('metrics', MetricasController::class,['parameters' => [
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
