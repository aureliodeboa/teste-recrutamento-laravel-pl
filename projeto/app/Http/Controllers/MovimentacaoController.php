<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimentacaoController extends Controller
{
    public function index($funcionarioId)
    {
        $movimentacoes = DB::select("SELECT * FROM movimentacoes WHERE funcionario_id = $funcionarioId ORDER BY created_at DESC");

        return response()->json($movimentacoes);
    }

    public function store(Request $request, $funcionarioId)
    {
        $tipo  = $request->tipo;
        $valor = $request->valor;
        $descricao = $request->descricao;

        $funcionario = DB::select("SELECT * FROM funcionarios WHERE id = $funcionarioId AND deleted = 0");

        if (empty($funcionario)) {
            return response()->json(['erro' => 'Funcionário não encontrado'], 404);
        }

        $funcionario = $funcionario[0];
        $saldoAtual  = $funcionario->saldo;

        if ($tipo === 'saida') {
            if ($saldoAtual < $valor) {
                return response()->json(['erro' => 'Saldo insuficiente'], 422);
            }

            DB::insert("INSERT INTO movimentacoes (funcionario_id, tipo, valor, descricao, created_at) VALUES ($funcionarioId, 'saida', $valor, '$descricao', NOW())");

            $novoSaldo = $saldoAtual - $valor;
            DB::update("UPDATE funcionarios SET saldo = $novoSaldo WHERE id = $funcionarioId");

            return response()->json(['mensagem' => 'Saída registrada', 'saldo' => $novoSaldo]);
        }

        if ($tipo === 'entrada') {
            DB::insert("INSERT INTO movimentacoes (funcionario_id, tipo, valor, descricao, created_at) VALUES ($funcionarioId, 'entrada', $valor, '$descricao', NOW())");

            $novoSaldo = $saldoAtual + $valor;
            DB::update("UPDATE funcionarios SET saldo = $novoSaldo WHERE id = $funcionarioId");

            return response()->json(['mensagem' => 'Entrada registrada', 'saldo' => $novoSaldo]);
        }

        return response()->json(['erro' => 'Tipo inválido. Use "entrada" ou "saida"'], 422);
    }
}
