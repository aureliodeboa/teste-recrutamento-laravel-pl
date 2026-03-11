<?php

namespace Database\Factories;

use App\Models\Funcionario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class FuncionarioFactory extends Factory
{
    protected $model = Funcionario::class;

    public function definition(): array
    {
        return [
            'nome'  => fake()->name(),
            'login' => fake()->unique()->userName(),
            'senha' => Hash::make('123456'),
            'saldo' => 0,
        ];
    }
}
