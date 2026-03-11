<?php

use App\Models\Administrador;

it('realiza login com credenciais válidas', function () {
    $admin = Administrador::factory()->create();

    $response = $this->postJson('/api/login', [
        'login' => $admin->login,
        'senha' => '123456',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['mensagem', 'token', 'admin'])
        ->assertJsonPath('admin.login', $admin->login);
});

it('rejeita login com senha incorreta', function () {
    $admin = Administrador::factory()->create();

    $response = $this->postJson('/api/login', [
        'login' => $admin->login,
        'senha' => 'errada',
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('erro', 'Credenciais inválidas');
});

it('rejeita login com login inexistente', function () {
    $response = $this->postJson('/api/login', [
        'login' => 'naoexiste',
        'senha' => '123456',
    ]);

    $response->assertStatus(401);
});

it('rejeita login sem campos obrigatórios', function () {
    $response = $this->postJson('/api/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['login', 'senha']);
});

it('não expõe a senha na resposta de login', function () {
    $admin = Administrador::factory()->create();

    $response = $this->postJson('/api/login', [
        'login' => $admin->login,
        'senha' => '123456',
    ]);

    $response->assertOk();
    $data = $response->json();
    expect($data['admin'])->not->toHaveKey('senha');
});

it('realiza logout com token válido', function () {
    $admin = Administrador::factory()->create();
    $token = $admin->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/logout');

    $response->assertOk()
        ->assertJsonPath('mensagem', 'Logout realizado');
});

it('rejeita acesso a rotas protegidas sem token', function () {
    $response = $this->getJson('/api/funcionarios');

    $response->assertStatus(401);
});
