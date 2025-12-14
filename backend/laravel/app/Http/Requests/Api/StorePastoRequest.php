<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePastoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100'],
            'tipo_pasto' => ['nullable', 'string', 'max:100'],
            'area_hectares' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'capacidade_animais' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'situacao' => ['required', Rule::in(['disponivel', 'ocupado', 'descanso', 'manutencao'])],
            'propriedade_id' => ['required', 'integer', 'exists:propriedades,id'],
            'lote_atual_id' => ['nullable', 'integer', 'exists:lotes,id'],
            'data_ultimo_descanso' => ['nullable', 'date', 'before_or_equal:today'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do pasto é obrigatório.',
            'nome.max' => 'O nome deve ter no máximo 100 caracteres.',
            
            'tipo_pasto.max' => 'O tipo de pasto deve ter no máximo 100 caracteres.',
            
            'area_hectares.required' => 'A área em hectares é obrigatória.',
            'area_hectares.numeric' => 'A área deve ser um número.',
            'area_hectares.min' => 'A área deve ser no mínimo 0.01 hectares.',
            'area_hectares.max' => 'A área deve ser no máximo 100.000 hectares.',
            
            'capacidade_animais.integer' => 'A capacidade deve ser um número inteiro.',
            'capacidade_animais.min' => 'A capacidade deve ser no mínimo 1 animal.',
            'capacidade_animais.max' => 'A capacidade deve ser no máximo 10.000 animais.',
            
            'situacao.required' => 'A situação do pasto é obrigatória.',
            'situacao.in' => 'Situação inválida. Use: disponivel, ocupado, descanso ou manutencao.',
            
            'propriedade_id.required' => 'A propriedade é obrigatória.',
            'propriedade_id.exists' => 'A propriedade selecionada não existe.',
            
            'lote_atual_id.exists' => 'O lote selecionado não existe.',
            
            'data_ultimo_descanso.date' => 'A data de descanso deve ser uma data válida.',
            'data_ultimo_descanso.before_or_equal' => 'A data de descanso não pode ser no futuro.',
            
            'observacoes.max' => 'As observações devem ter no máximo 1000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'tipo_pasto' => 'tipo de pasto',
            'area_hectares' => 'área em hectares',
            'capacidade_animais' => 'capacidade de animais',
            'situacao' => 'situação',
            'propriedade_id' => 'propriedade',
            'lote_atual_id' => 'lote atual',
            'data_ultimo_descanso' => 'data do último descanso',
            'observacoes' => 'observações',
        ];
    }
}

