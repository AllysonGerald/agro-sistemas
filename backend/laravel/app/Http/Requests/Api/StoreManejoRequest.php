<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManejoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_atividade' => ['required', Rule::in(['pesagem', 'vacinacao', 'tratamento', 'reproducao', 'movimentacao', 'nutricao', 'outros'])],
            'data_realizacao' => ['required', 'date'],
            'animal_id' => ['nullable', 'integer', 'exists:animais,id', 'required_without:lote_id'],
            'lote_id' => ['nullable', 'integer', 'exists:lotes,id', 'required_without:animal_id'],
            'propriedade_id' => ['required', 'integer', 'exists:propriedades,id'],
            'responsavel' => ['nullable', 'string', 'max:100'],
            'descricao' => ['required', 'string', 'max:500'],
            'detalhes' => ['nullable', 'json'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_atividade.required' => 'O tipo de atividade é obrigatório.',
            'tipo_atividade.in' => 'Tipo de atividade inválido.',
            
            'data_realizacao.required' => 'A data de realização é obrigatória.',
            'data_realizacao.date' => 'A data de realização deve ser uma data válida.',
            
            'animal_id.exists' => 'O animal selecionado não existe.',
            'animal_id.required_without' => 'Selecione um animal ou um lote.',
            
            'lote_id.exists' => 'O lote selecionado não existe.',
            'lote_id.required_without' => 'Selecione um lote ou um animal.',
            
            'propriedade_id.required' => 'A propriedade é obrigatória.',
            'propriedade_id.exists' => 'A propriedade selecionada não existe.',
            
            'responsavel.max' => 'O responsável deve ter no máximo 100 caracteres.',
            
            'descricao.required' => 'A descrição é obrigatória.',
            'descricao.max' => 'A descrição deve ter no máximo 500 caracteres.',
            
            'detalhes.json' => 'Os detalhes devem estar em formato JSON válido.',
            'observacoes.max' => 'As observações devem ter no máximo 1000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'tipo_atividade' => 'tipo de atividade',
            'data_realizacao' => 'data de realização',
            'animal_id' => 'animal',
            'lote_id' => 'lote',
            'propriedade_id' => 'propriedade',
            'responsavel' => 'responsável',
            'descricao' => 'descrição',
            'detalhes' => 'detalhes',
            'observacoes' => 'observações',
        ];
    }
}

