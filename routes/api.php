<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ViagemController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\UserController;

// Rotas públicas para autenticação
Route::post('/register', [AuthController::class, 'register']); // Registo de novos utilizadores
Route::post('/login', [AuthController::class, 'login']); // Login de utilizadores

// Rotas protegidas por autenticação
Route::middleware('auth:api')->group(function () {
    // Rotas de perfil e autenticação
    Route::get('/perfil', [AuthController::class, 'perfil']); // Ver perfil do utilizador autenticado
    Route::post('/logout', [AuthController::class, 'logout']); // Terminar sessão

    // Rotas de acesso do utilizador comum
    Route::get('/viagens', [ViagemController::class, 'index']); // Listar todas as viagens
    Route::get('/viagens/{id}', [ViagemController::class, 'show']); // Ver detalhes de uma viagem
    Route::post('/reservas', [ReservaController::class, 'store']); // Criar uma nova reserva
    Route::get('/reservas', [ReservaController::class, 'index']); // Listar apenas as reservas do próprio utilizador
    
    // Gestão da própria conta e reservas
    Route::delete('/user', [UserController::class, 'destroySelf']); // Eliminar a própria conta
    Route::delete('/reservas/{id}', [ReservaController::class, 'destroy']); // Eliminar uma reserva do próprio utilizador

    // Rotas de administração (CRUD completo)
    Route::middleware(['auth:api', 'is.admin'])->group(function () {
        // Gestão de viagens (exceto listar e ver detalhes, que são públicas)
        Route::apiResource('viagens', ViagemController::class)->except(['index', 'show']);
        
        // Gestão de reservas (admin)
        Route::put('/reservas/admin/{id}', [ReservaController::class, 'adminUpdate']); // Atualizar qualquer reserva (admin)
        Route::delete('/reservas/admin/{id}', [ReservaController::class, 'adminDestroy']); // Eliminar qualquer reserva
        
        // Usar apenas as rotas específicas que o admin precisa, evitando conflito com as rotas de usuário comum
        Route::get('/reservas/admin', [ReservaController::class, 'adminIndex']); // Listar todas as reservas (admin)
        Route::get('/reservas/admin/{id}', [ReservaController::class, 'adminShow']); // Ver detalhes de qualquer reserva (admin)
        Route::post('/reservas/admin', [ReservaController::class, 'adminStore']); // Criar reserva para qualquer usuário (admin)
        
        // Remover esta linha para evitar conflito
        // Route::apiResource('reservas', ReservaController::class)->except(['store']);

        // Gestão de utilizadores
        Route::get('/users', [UserController::class, 'index']); // Ver todos os utilizadores
        Route::get('/users/{id}', [UserController::class, 'show']); // Ver informações de utilizador específico
        Route::post('/users', [UserController::class, 'store']); // Criar novo utilizador
        Route::put('/users/{id}', [UserController::class, 'update']); // Atualizar utilizador
        Route::delete('/users/{id}', [UserController::class, 'adminDestroy']); // Apagar utilizador
    });
});




