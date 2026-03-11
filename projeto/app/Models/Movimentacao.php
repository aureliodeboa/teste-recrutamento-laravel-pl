<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model
{
    public $timestamps = false;

    protected $table = 'movimentacoes';

    protected $fillable = [
        'funcionario_id',
        'tipo',
        'valor',
        'descricao',
        'created_at',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
