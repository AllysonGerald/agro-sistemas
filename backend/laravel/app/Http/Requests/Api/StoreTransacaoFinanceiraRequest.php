<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransacaoFinanceiraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['receita', 'despesa'])],
            'categoria_id' => ['required', 'integer', 'exists:categorias_financeiras,id'],
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'data' => ['required', 'date'],
            'animal_id' => ['nullable', 'integer', 'exists:animais,id'],
            'lote_id' => ['nullable', 'integer', 'exists:lotes,id'],
            'propriedade_id' => ['required', 'integer', 'exists:propriedades,id'],
            'forma_pagamento' => ['nullable', 'string', 'max:50'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'O tipo da transação é obrigatório.',
            'tipo.in' => 'O tipo deve ser "receita" ou "despesa".',
            
            'categoria_id.required' => 'A categoria é obrigatória.',
            'categoria_id.exists' => 'A categoria selecionada não existe.',
            
            'descricao.required' => 'A descrição é obrigatória.',
            'descricao.max' => 'A descrição deve ter no máximo 255 caracteres.',
            
            'valor.required' => 'O valor é obrigatório.',
            'valor.numeric' => 'O valor deve ser um número.',
            'valor.min' => 'O valor deve ser no mínimo R$ 0,01.',
            'valor.max' => 'O valor deve ser no máximo R$ 999.999.999,99.',
            
            'data.required' => 'A data é obrigatória.',
            'data.date' => 'A data deve ser uma data válida.',
            
            'animal_id.exists' => 'O animal selecionado não existe.',
            'lote_id.exists' => 'O lote selecionado não existe.',
            
            'propriedade_id.required' => 'A propriedade é obrigatória.',
            'propriedade_id.exists' => 'A propriedade selecionada não existe.',
            
            'forma_pagamento.max' => 'A forma de pagamento deve ter no máximo 50 caracteres.',
            'observacoes.max' => 'As observações devem ter no máximo 1000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'tipo' => 'tipo',
            'categoria_id' => 'categoria',
            'descricao' => 'descrição',
            'valor' => 'valor',
            'data' => 'data',
            'animal_id' => 'animal',
            'lote_id' => 'lote',
            'propriedade_id' => 'propriedade',
            'forma_pagamento' => 'forma de pagamento',
            'observacoes' => 'observações',
        ];
    }
}

