<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovimentacaoRequest;
use App\Http\Resources\MovimentacaoResource;
use App\Jobs\ProcessarMovimentacao;
use App\Models\Funcionario;
use App\Services\MovimentacaoService;
use Illuminate\Http\JsonResponse;

class MovimentacaoController extends Controller
{
    public function __construct(
        private MovimentacaoService $movimentacaoService
    ) {}

    public function index(Funcionario $funcionario): JsonResponse
    {
        $movimentacoes = $funcionario->movimentacoes()
            ->orderByDesc('created_at')
            ->paginate(15);

        return MovimentacaoResource::collection($movimentacoes)->response();
    }

    public function store(StoreMovimentacaoRequest $request, Funcionario $funcionario): JsonResponse
    {
        try {
            $resultado = $this->movimentacaoService->registrar(
                $funcionario,
                $request->tipo,
                $request->valor,
                $request->descricao,
            );

            $tipo = $request->tipo === 'entrada' ? 'Entrada' : 'Saída';

            return response()->json([
                'mensagem'     => "{$tipo} registrada",
                'saldo'        => $resultado['saldo'],
                'movimentacao' => new MovimentacaoResource($resultado['movimentacao']),
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['erro' => $e->getMessage()], 422);
        }
    }

    public function storeAsync(StoreMovimentacaoRequest $request, Funcionario $funcionario): JsonResponse
    {
        ProcessarMovimentacao::dispatch(
            $funcionario,
            $request->tipo,
            $request->valor,
            $request->descricao,
        );

        return response()->json([
            'mensagem' => 'Movimentação enviada para processamento',
        ], 202);
    }
}
