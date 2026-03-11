<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Funcionario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'funcionarios';

    protected $fillable = [
        'nome',
        'login',
        'senha',
        'saldo',
    ];

    protected $hidden = [
        'senha',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    public function movimentacoes()
    {
        return $this->hasMany(Movimentacao::class);
    }
}
