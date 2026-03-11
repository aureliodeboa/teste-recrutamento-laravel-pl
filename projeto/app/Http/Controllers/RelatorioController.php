<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\JsonResponse;

class RelatorioController extends Controller
{
    public function index(): JsonResponse
    {
        $resultado = Funcionario::query()
            ->leftJoin('movimentacoes', 'funcionarios.id', '=', 'movimentacoes.funcionario_id')
            ->selectRaw('
                funcionarios.id,
                funcionarios.nome,
                funcionarios.saldo,
                COALESCE(SUM(CASE WHEN movimentacoes.tipo = "entrada" THEN movimentacoes.valor ELSE 0 END), 0) as total_entradas,
                COALESCE(SUM(CASE WHEN movimentacoes.tipo = "saida" THEN movimentacoes.valor ELSE 0 END), 0) as total_saidas,
                COUNT(movimentacoes.id) as movimentacoes_count
            ')
            ->whereNull('funcionarios.deleted_at')
            ->groupBy('funcionarios.id', 'funcionarios.nome', 'funcionarios.saldo')
            ->paginate(15);

        return response()->json($resultado);
    }
}
