<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\MovimentacaoController;
use App\Http\Controllers\RelatorioController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('funcionarios', FuncionarioController::class);

    Route::get('/funcionarios/{funcionario}/movimentacoes', [MovimentacaoController::class, 'index']);
    Route::post('/funcionarios/{funcionario}/movimentacoes', [MovimentacaoController::class, 'store']);
    Route::post('/funcionarios/{funcionario}/movimentacoes/async', [MovimentacaoController::class, 'storeAsync']);

    Route::get('/relatorio', [RelatorioController::class, 'index']);
});
