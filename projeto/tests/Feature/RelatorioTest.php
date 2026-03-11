<?php

use App\Models\Administrador;
use App\Models\Funcionario;
use App\Models\Movimentacao;

beforeEach(function () {
    $this->admin = Administrador::factory()->create();
    $this->token = $this->admin->createToken('test')->plainTextToken;
});

it('retorna relatório com dados agregados corretos', function () {
    $func = Funcionario::factory()->create(['saldo' => 0]);

    Movimentacao::create([
        'funcionario_id' => $func->id,
        'tipo' => 'entrada',
        'valor' => 100,
        'created_at' => now(),
    ]);

    Movimentacao::create([
        'funcionario_id' => $func->id,
        'tipo' => 'saida',
        'valor' => 30,
        'created_at' => now(),
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/relatorio');

    $response->assertOk();

    $data = collect($response->json('data'));
    $item = $data->firstWhere('id', $func->id);

    expect((float) $item['total_entradas'])->toBe(100.0);
    expect((float) $item['total_saidas'])->toBe(30.0);
    expect((int) $item['movimentacoes_count'])->toBe(2);
});

it('não inclui funcionários deletados no relatório', function () {
    $ativo = Funcionario::factory()->create();
    $deletado = Funcionario::factory()->create();
    $deletado->delete();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/relatorio');

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->toContain($ativo->id);
    expect($ids)->not->toContain($deletado->id);
});

it('rejeita acesso ao relatório sem autenticação', function () {
    $response = $this->getJson('/api/relatorio');

    $response->assertStatus(401);
});
