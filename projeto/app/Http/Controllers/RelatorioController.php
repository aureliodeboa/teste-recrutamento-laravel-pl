<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RelatorioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $page = $request->integer('page', 1);

        // Versão global incrementada pelo MovimentacaoService a cada escrita.
        // Assim todas as páginas são invalidadas de uma vez sem precisar conhecer as chaves.
        $version = Cache::get('relatorio_version', 0);

        $resultado = Cache::remember("relatorio_v{$version}_page_{$page}", now()->addMinutes(5), function () use ($page) {
            return Funcionario::query()
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
                ->paginate(15, ['*'], 'page', $page);
        });

        return response()->json($resultado);
    }
}
