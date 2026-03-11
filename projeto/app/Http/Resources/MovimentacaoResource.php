<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovimentacaoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'funcionario_id' => $this->funcionario_id,
            'tipo'           => $this->tipo,
            'valor'          => $this->valor,
            'descricao'      => $this->descricao,
            'created_at'     => $this->created_at,
        ];
    }
}
