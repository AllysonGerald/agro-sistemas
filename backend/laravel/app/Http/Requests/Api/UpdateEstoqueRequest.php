<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstoqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:150'],
            'tipo' => ['required', Rule::in(['racao', 'medicamento', 'vacina', 'suplemento', 'equipamento', 'outros'])],
            'unidade' => ['required', 'string', 'max:20'],
            'quantidade_atual' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'quantidade_minima' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'valor_unitario' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'data_validade' => ['nullable', 'date'],
            'propriedade_id' => ['required', 'integer', 'exists:propriedades,id'],
            'localizacao' => ['nullable', 'string', 'max:150'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do item é obrigatório.',
            'nome.max' => 'O nome deve ter no máximo 150 caracteres.',
            
            'tipo.required' => 'O tipo do item é obrigatório.',
            'tipo.in' => 'Tipo inválido. Use: racao, medicamento, vacina, suplemento, equipamento ou outros.',
            
            'unidade.required' => 'A unidade de medida é obrigatória.',
            'unidade.max' => 'A unidade deve ter no máximo 20 caracteres.',
            
            'quantidade_atual.required' => 'A quantidade atual é obrigatória.',
            'quantidade_atual.numeric' => 'A quantidade atual deve ser um número.',
            'quantidade_atual.min' => 'A quantidade atual não pode ser negativa.',
            'quantidade_atual.max' => 'A quantidade atual deve ser no máximo 999.999.999.',
            
            'quantidade_minima.required' => 'A quantidade mínima é obrigatória.',
            'quantidade_minima.numeric' => 'A quantidade mínima deve ser um número.',
            'quantidade_minima.min' => 'A quantidade mínima não pode ser negativa.',
            'quantidade_minima.max' => 'A quantidade mínima deve ser no máximo 999.999.999.',
            
            'valor_unitario.numeric' => 'O valor unitário deve ser um número.',
            'valor_unitario.min' => 'O valor unitário não pode ser negativo.',
            'valor_unitario.max' => 'O valor unitário deve ser no máximo R$ 999.999,99.',
            
            'data_validade.date' => 'A data de validade deve ser uma data válida.',
            
            'propriedade_id.required' => 'A propriedade é obrigatória.',
            'propriedade_id.exists' => 'A propriedade selecionada não existe.',
            
            'localizacao.max' => 'A localização deve ter no máximo 150 caracteres.',
            'observacoes.max' => 'As observações devem ter no máximo 1000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'tipo' => 'tipo',
            'unidade' => 'unidade',
            'quantidade_atual' => 'quantidade atual',
            'quantidade_minima' => 'quantidade mínima',
            'valor_unitario' => 'valor unitário',
            'data_validade' => 'data de validade',
            'propriedade_id' => 'propriedade',
            'localizacao' => 'localização',
            'observacoes' => 'observações',
        ];
    }
}

