<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\MovimentacaoController;
use App\Http\Controllers\RelatorioController;
use Illuminate\Support\Facades\Route;

// Autenticação
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Funcionários
Route::get('/funcionarios',          [FuncionarioController::class, 'index']);
Route::get('/funcionarios/{id}',     [FuncionarioController::class, 'show']);
Route::post('/funcionarios',         [FuncionarioController::class, 'store']);
Route::put('/funcionarios/{id}',     [FuncionarioController::class, 'update']);
Route::delete('/funcionarios/{id}',  [FuncionarioController::class, 'destroy']);

// Movimentações
Route::get('/funcionarios/{id}/movimentacoes',  [MovimentacaoController::class, 'index']);
Route::post('/funcionarios/{id}/movimentacoes', [MovimentacaoController::class, 'store']);

// Relatórios
Route::get('/relatorio', [RelatorioController::class, 'index']);
