<?php

namespace App\Jobs;

use App\Models\Funcionario;
use App\Services\MovimentacaoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessarMovimentacao implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Funcionario $funcionario,
        public string $tipo,
        public float $valor,
        public ?string $descricao,
    ) {}

    public function handle(MovimentacaoService $service): void
    {
        $resultado = $service->registrar(
            $this->funcionario,
            $this->tipo,
            $this->valor,
            $this->descricao,
        );

        Log::info('Movimentação processada', [
            'funcionario_id' => $this->funcionario->id,
            'tipo'           => $this->tipo,
            'valor'          => $this->valor,
            'novo_saldo'     => $resultado['saldo'],
        ]);
    }
}
