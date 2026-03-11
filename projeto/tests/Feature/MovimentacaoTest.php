<?php

use App\Models\Administrador;
use App\Models\Funcionario;
use App\Models\Movimentacao;

beforeEach(function () {
    $this->admin = Administrador::factory()->create();
    $this->token = $this->admin->createToken('test')->plainTextToken;
});

it('registra uma entrada com sucesso', function () {
    $func = Funcionario::factory()->create(['saldo' => 100]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/funcionarios/{$func->id}/movimentacoes", [
            'tipo'      => 'entrada',
            'valor'     => 50.00,
            'descricao' => 'Bônus mensal',
        ]);

    $response->assertStatus(201);
    expect((float) $response->json('saldo'))->toBe(150.0);

    $this->assertDatabaseHas('funcionarios', [
        'id'    => $func->id,
        'saldo' => 150.00,
    ]);
});

it('registra uma saída com sucesso', function () {
    $func = Funcionario::factory()->create(['saldo' => 100]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/funcionarios/{$func->id}/movimentacoes", [
            'tipo'      => 'saida',
            'valor'     => 30.00,
            'descricao' => 'Resgate produto',
        ]);

    $response->assertStatus(201);
    expect((float) $response->json('saldo'))->toBe(70.0);
});

it('rejeita saída com saldo insuficiente', function () {
    $func = Funcionario::factory()->create(['saldo' => 10]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/funcionarios/{$func->id}/movimentacoes", [
            'tipo'  => 'saida',
            'valor' => 50.00,
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('erro', 'Saldo insuficiente');

    $this->assertDatabaseHas('funcionarios', [
        'id'    => $func->id,
        'saldo' => 10.00,
    ]);
});

it('rejeita valor negativo', function () {
    $func = Funcionario::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/funcionarios/{$func->id}/movimentacoes", [
            'tipo'  => 'entrada',
            'valor' => -10,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['valor']);
});

it('rejeita valor zero', function () {
    $func = Funcionario::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/funcionarios/{$func->id}/movimentacoes", [
            'tipo'  => 'entrada',
            'valor' => 0,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['valor']);
});

it('rejeita tipo inválido', function () {
    $func = Funcionario::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/funcionarios/{$func->id}/movimentacoes", [
            'tipo'  => 'invalido',
            'valor' => 10,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['tipo']);
});

it('rejeita movimentação sem campos obrigatórios', function () {
    $func = Funcionario::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/funcionarios/{$func->id}/movimentacoes", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['tipo', 'valor']);
});

it('lista movimentações de um funcionário com paginação', function () {
    $func = Funcionario::factory()->create();

    for ($i = 0; $i < 20; $i++) {
        Movimentacao::create([
            'funcionario_id' => $func->id,
            'tipo'           => 'entrada',
            'valor'          => 10,
            'descricao'      => "Mov {$i}",
            'created_at'     => now(),
        ]);
    }

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/funcionarios/{$func->id}/movimentacoes");

    $response->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta']);

    expect($response->json('data'))->toHaveCount(15);
});

it('retorna 404 para movimentação de funcionário inexistente', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/funcionarios/999/movimentacoes', [
            'tipo'  => 'entrada',
            'valor' => 10,
        ]);

    $response->assertStatus(404);
});

it('garante atomicidade — saldo bate com movimentações', function () {
    $func = Funcionario::factory()->create(['saldo' => 0]);

    $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/funcionarios/{$func->id}/movimentacoes", [
            'tipo' => 'entrada', 'valor' => 100, 'descricao' => 'Crédito',
        ]);

    $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/funcionarios/{$func->id}/movimentacoes", [
            'tipo' => 'saida', 'valor' => 30, 'descricao' => 'Débito',
        ]);

    $func->refresh();
    $somaEntradas = Movimentacao::where('funcionario_id', $func->id)
        ->where('tipo', 'entrada')->sum('valor');
    $somaSaidas = Movimentacao::where('funcionario_id', $func->id)
        ->where('tipo', 'saida')->sum('valor');

    expect((float) $func->saldo)->toBe((float) ($somaEntradas - $somaSaidas));
});
