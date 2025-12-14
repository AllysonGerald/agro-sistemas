<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100'],
            'descricao' => ['nullable', 'string', 'max:500'],
            'propriedade_id' => ['required', 'integer', 'exists:propriedades,id'],
            'pasto_id' => ['nullable', 'integer', 'exists:pastos,id'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do lote é obrigatório.',
            'nome.max' => 'O nome deve ter no máximo 100 caracteres.',
            
            'descricao.max' => 'A descrição deve ter no máximo 500 caracteres.',
            
            'propriedade_id.required' => 'A propriedade é obrigatória.',
            'propriedade_id.exists' => 'A propriedade selecionada não existe.',
            
            'pasto_id.exists' => 'O pasto selecionado não existe.',
            
            'observacoes.max' => 'As observações devem ter no máximo 1000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'descricao' => 'descrição',
            'propriedade_id' => 'propriedade',
            'pasto_id' => 'pasto',
            'observacoes' => 'observações',
        ];
    }
}

