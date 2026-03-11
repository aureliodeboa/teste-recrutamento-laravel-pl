<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovimentacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo'      => ['required', Rule::in(['entrada', 'saida'])],
            'valor'     => ['required', 'numeric', 'gt:0'],
            'descricao' => ['nullable', 'string', 'max:255'],
        ];
    }
}
