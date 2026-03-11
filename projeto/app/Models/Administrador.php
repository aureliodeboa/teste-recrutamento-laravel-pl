<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Administrador extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'administradores';

    protected $fillable = [
        'nome',
        'login',
        'senha',
    ];

    protected $hidden = [
        'senha',
        'token',
    ];

    public function getAuthPassword(): string
    {
        return $this->senha;
    }
}
