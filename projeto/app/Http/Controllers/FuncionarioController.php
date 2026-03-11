<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFuncionarioRequest;
use App\Http\Requests\UpdateFuncionarioRequest;
use App\Http\Resources\FuncionarioResource;
use App\Models\Funcionario;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class FuncionarioController extends Controller
{
    public function index(): JsonResponse
    {
        $funcionarios = Funcionario::paginate(15);

        return FuncionarioResource::collection($funcionarios)->response();
    }

    public function show(Funcionario $funcionario): FuncionarioResource
    {
        return new FuncionarioResource($funcionario);
    }

    public function store(StoreFuncionarioRequest $request): JsonResponse
    {
        $funcionario = Funcionario::create([
            'nome'  => $request->nome,
            'login' => $request->login,
            'senha' => Hash::make($request->senha),
            'saldo' => 0,
        ]);

        return response()->json([
            'mensagem' => 'Funcionário criado',
            'funcionario' => new FuncionarioResource($funcionario),
        ], 201);
    }

    public function update(UpdateFuncionarioRequest $request, Funcionario $funcionario): JsonResponse
    {
        $funcionario->update([
            'nome'  => $request->nome,
            'login' => $request->login,
        ]);

        return response()->json([
            'mensagem' => 'Funcionário atualizado',
            'funcionario' => new FuncionarioResource($funcionario),
        ]);
    }

    public function destroy(Funcionario $funcionario): JsonResponse
    {
        $funcionario->delete();

        return response()->json(['mensagem' => 'Funcionário removido']);
    }
}
