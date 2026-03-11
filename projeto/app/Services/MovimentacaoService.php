<?php

namespace App\Services;

use App\Models\Funcionario;
use App\Models\Movimentacao;
use Illuminate\Support\Facades\DB;

class MovimentacaoService
{
    public function registrar(Funcionario $funcionario, string $tipo, float $valor, ?string $descricao): array
    {
        return DB::transaction(function () use ($funcionario, $tipo, $valor, $descricao) {
            $funcionario = Funcionario::lockForUpdate()->findOrFail($funcionario->id);

            if ($tipo === 'saida' && $funcionario->saldo < $valor) {
                throw new \DomainException('Saldo insuficiente');
            }

            $movimentacao = Movimentacao::create([
                'funcionario_id' => $funcionario->id,
                'tipo'           => $tipo,
                'valor'          => $valor,
                'descricao'      => $descricao,
                'created_at'     => now(),
            ]);

            $novoSaldo = $tipo === 'entrada'
                ? $funcionario->saldo + $valor
                : $funcionario->saldo - $valor;

            $funcionario->update(['saldo' => $novoSaldo]);

            return [
                'movimentacao' => $movimentacao,
                'saldo'        => $novoSaldo,
            ];
        });
    }
}
