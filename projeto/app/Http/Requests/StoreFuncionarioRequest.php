<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFuncionarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'  => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:255', 'unique:funcionarios,login'],
            'senha' => ['required', 'string', 'min:6'],
        ];
    }
}
