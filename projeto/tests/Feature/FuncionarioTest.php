<?php

use App\Models\Administrador;
use App\Models\Funcionario;

beforeEach(function () {
    $this->admin = Administrador::factory()->create();
    $this->token = $this->admin->createToken('test')->plainTextToken;
});

it('lista funcionários com paginação', function () {
    Funcionario::factory()->count(20)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/funcionarios');

    $response->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta']);

    expect($response->json('data'))->toHaveCount(15);
});

it('exibe um funcionário específico', function () {
    $func = Funcionario::factory()->create(['nome' => 'João']);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/funcionarios/{$func->id}");

    $response->assertOk()
        ->assertJsonPath('data.nome', 'João');
});

it('retorna 404 para funcionário inexistente', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/funcionarios/999');

    $response->assertStatus(404);
});

it('cria um funcionário com dados válidos', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/funcionarios', [
            'nome'  => 'Novo Funcionário',
            'login' => 'novo.func',
            'senha' => '123456',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('mensagem', 'Funcionário criado');

    $this->assertDatabaseHas('funcionarios', ['login' => 'novo.func']);
});

it('rejeita criação com login duplicado', function () {
    Funcionario::factory()->create(['login' => 'duplicado']);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/funcionarios', [
            'nome'  => 'Outro',
            'login' => 'duplicado',
            'senha' => '123456',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['login']);
});

it('rejeita criação sem campos obrigatórios', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/funcionarios', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['nome', 'login', 'senha']);
});

it('atualiza um funcionário', function () {
    $func = Funcionario::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->putJson("/api/funcionarios/{$func->id}", [
            'nome'  => 'Nome Atualizado',
            'login' => 'login.atualizado',
        ]);

    $response->assertOk()
        ->assertJsonPath('mensagem', 'Funcionário atualizado');

    $this->assertDatabaseHas('funcionarios', [
        'id'    => $func->id,
        'nome'  => 'Nome Atualizado',
        'login' => 'login.atualizado',
    ]);
});

it('realiza soft delete de funcionário', function () {
    $func = Funcionario::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->deleteJson("/api/funcionarios/{$func->id}");

    $response->assertOk()
        ->assertJsonPath('mensagem', 'Funcionário removido');

    $this->assertSoftDeleted('funcionarios', ['id' => $func->id]);
});

it('não lista funcionários deletados', function () {
    $func = Funcionario::factory()->create();
    $func->delete();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/funcionarios');

    $data = $response->json('data');
    $ids = collect($data)->pluck('id')->all();
    expect($ids)->not->toContain($func->id);
});

it('não expõe a senha do funcionário nas respostas', function () {
    $func = Funcionario::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/funcionarios/{$func->id}");

    expect($response->json('data'))->not->toHaveKey('senha');
});
