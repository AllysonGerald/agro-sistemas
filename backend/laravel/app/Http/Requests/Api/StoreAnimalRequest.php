<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnimalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identificacao' => ['required', 'string', 'max:50', 'unique:animais,identificacao'],
            'nome_numero' => ['nullable', 'string', 'max:50'],
            'sexo' => ['required', Rule::in(['macho', 'femea'])],
            'raca' => ['nullable', 'string', 'max:100'],
            'categoria_atual' => ['nullable', Rule::in(['bezerro', 'bezerra', 'novilho', 'novilha', 'boi', 'vaca', 'touro'])],
            'situacao' => ['required', Rule::in(['ativo', 'vendido', 'morto', 'transferido'])],
            'data_nascimento' => ['nullable', 'date', 'before_or_equal:today'],
            'data_entrada' => ['nullable', 'date', 'before_or_equal:today'],
            'idade_meses' => ['nullable', 'integer', 'min:0', 'max:300'],
            'peso_entrada' => ['nullable', 'numeric', 'min:0', 'max:3000'],
            'peso_atual' => ['nullable', 'numeric', 'min:0', 'max:3000'],
            'propriedade_id' => ['required', 'integer', 'exists:propriedades,id'],
            'lote_id' => ['nullable', 'integer', 'exists:lotes,id'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'identificacao.required' => 'A identificação do animal é obrigatória.',
            'identificacao.unique' => 'Já existe um animal com esta identificação.',
            'identificacao.max' => 'A identificação deve ter no máximo 50 caracteres.',
            
            'sexo.required' => 'O sexo do animal é obrigatório.',
            'sexo.in' => 'O sexo deve ser "macho" ou "fêmea".',
            
            'categoria_atual.in' => 'Categoria inválida. Use: bezerro, bezerra, novilho, novilha, boi, vaca ou touro.',
            
            'situacao.required' => 'A situação do animal é obrigatória.',
            'situacao.in' => 'Situação inválida. Use: ativo, vendido, morto ou transferido.',
            
            'data_nascimento.date' => 'A data de nascimento deve ser uma data válida.',
            'data_nascimento.before_or_equal' => 'A data de nascimento não pode ser no futuro.',
            
            'data_entrada.date' => 'A data de entrada deve ser uma data válida.',
            'data_entrada.before_or_equal' => 'A data de entrada não pode ser no futuro.',
            
            'idade_meses.integer' => 'A idade deve ser um número inteiro.',
            'idade_meses.min' => 'A idade não pode ser negativa.',
            'idade_meses.max' => 'A idade deve ser no máximo 300 meses.',
            
            'peso_entrada.numeric' => 'O peso de entrada deve ser um número.',
            'peso_entrada.min' => 'O peso de entrada não pode ser negativo.',
            'peso_entrada.max' => 'O peso de entrada deve ser no máximo 3000 kg.',
            
            'peso_atual.numeric' => 'O peso atual deve ser um número.',
            'peso_atual.min' => 'O peso atual não pode ser negativo.',
            'peso_atual.max' => 'O peso atual deve ser no máximo 3000 kg.',
            
            'propriedade_id.required' => 'A propriedade é obrigatória.',
            'propriedade_id.exists' => 'A propriedade selecionada não existe.',
            
            'lote_id.exists' => 'O lote selecionado não existe.',
            
            'observacoes.max' => 'As observações devem ter no máximo 1000 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'identificacao' => 'identificação',
            'nome_numero' => 'nome/número',
            'sexo' => 'sexo',
            'raca' => 'raça',
            'categoria_atual' => 'categoria',
            'situacao' => 'situação',
            'data_nascimento' => 'data de nascimento',
            'data_entrada' => 'data de entrada',
            'idade_meses' => 'idade em meses',
            'peso_entrada' => 'peso de entrada',
            'peso_atual' => 'peso atual',
            'propriedade_id' => 'propriedade',
            'lote_id' => 'lote',
            'observacoes' => 'observações',
        ];
    }
}

