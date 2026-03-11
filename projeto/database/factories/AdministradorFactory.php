<?php

namespace Database\Factories;

use App\Models\Administrador;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AdministradorFactory extends Factory
{
    protected $model = Administrador::class;

    public function definition(): array
    {
        return [
            'nome'  => fake()->name(),
            'login' => fake()->unique()->userName(),
            'senha' => Hash::make('123456'),
        ];
    }
}
