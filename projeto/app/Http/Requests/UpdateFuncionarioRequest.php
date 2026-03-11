<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFuncionarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $funcionarioId = $this->route('funcionario');

        return [
            'nome'  => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:255', Rule::unique('funcionarios')->ignore($funcionarioId)],
        ];
    }
}
