<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriaFinanceiraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100'],
            'tipo' => ['required', Rule::in(['receita', 'despesa'])],
            'descricao' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome da categoria é obrigatório.',
            'nome.max' => 'O nome deve ter no máximo 100 caracteres.',
            
            'tipo.required' => 'O tipo da categoria é obrigatório.',
            'tipo.in' => 'O tipo deve ser "receita" ou "despesa".',
            
            'descricao.max' => 'A descrição deve ter no máximo 500 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'tipo' => 'tipo',
            'descricao' => 'descrição',
        ];
    }
}

