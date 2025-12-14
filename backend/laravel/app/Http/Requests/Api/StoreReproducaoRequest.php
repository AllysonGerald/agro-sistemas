<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReproducaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'animal_mae_id' => ['required', 'integer', 'exists:animais,id'],
            'animal_pai_id' => ['nullable', 'integer', 'exists:animais,id'],
            'data_cobertura' => ['nullable', 'date', 'before_or_equal:today'],
            'data_prevista_parto' => ['nullable', 'date', 'after_or_equal:data_cobertura'],
            'data_parto' => ['nullable', 'date', 'after_or_equal:data_cobertura', 'before_or_equal:today'],
            'tipo_reproducao' => ['nullable', Rule::in(['monta_natural', 'inseminacao_artificial', 'transferencia_embriao'])],
            'animal_cria_id' => ['nullable', 'integer', 'exists:animais,id'],
            'status' => ['required', Rule::in(['prenha', 'parto_normal', 'parto_complicacao', 'aborto', 'aguardando_confirmacao'])],
            'propriedade_id' => ['required', 'integer', 'exists:propriedades,id'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'animal_mae_id.required' => 'O animal (mãe) é obrigatório.',
            'animal_mae_id.exists' => 'O animal selecionado (mãe) não existe.',
            
            'animal_pai_id.exists' => 'O animal selecionado (pai) não existe.',
            
            'data_cobertura.date' => 'A data de cobertura deve ser uma data válida.',
            'data_cobertura.before_or_equal' => 'A data de cobertura não pode ser no futuro.',
            
            'data_prevista_parto.date' => 'A data prevista do parto deve ser uma data válida.',
            'data_prevista_parto.after_or_equal' => 'A data prevista do parto deve ser posterior à cobertura.',
            
            'data_parto.date' => 'A data do parto deve ser uma data válida.',
            'data_parto.after_or_equal' => 'A data do parto deve ser posterior à cobertura.',
            'data_parto.before_or_equal' => 'A data do parto não pode ser no futuro.',
            
            'tipo_reproducao.in' => 'Tipo de reprodução inválido.',
            
            'animal_cria_id.exists' => 'O animal selecionado (cria) não existe.',
            
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'Status inválido.',
            
            'propriedade_id.required' => 'A propriedade é obrigatória.',
            'propriedade_id.exists' => 'A propriedade selecionada não existe.',
            
            'observacoes.max' => 'As observações devem ter no máximo 1000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'animal_mae_id' => 'animal (mãe)',
            'animal_pai_id' => 'animal (pai)',
            'data_cobertura' => 'data de cobertura',
            'data_prevista_parto' => 'data prevista do parto',
            'data_parto' => 'data do parto',
            'tipo_reproducao' => 'tipo de reprodução',
            'animal_cria_id' => 'animal (cria)',
            'status' => 'status',
            'propriedade_id' => 'propriedade',
            'observacoes' => 'observações',
        ];
    }
}

