<?php

namespace App\Http\Requests;

use App\Enums\AnimalSpeciesEnum;
use App\Enums\LivestockPurposeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRebanhoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'especie' => [
                'required',
                'string',
                Rule::in(array_column(AnimalSpeciesEnum::cases(), 'value'))
            ],
            'quantidade' => ['required', 'integer', 'min:1', 'max:999999'],
            'finalidade' => [
                'required',
                'string',
                Rule::in(array_column(LivestockPurposeEnum::cases(), 'value'))
            ],
            'data_atualizacao' => ['nullable', 'date'],
            'propriedade_id' => ['required', 'exists:propriedades,id']
        ];
    }

    public function messages(): array
    {
        return [
            'especie.required' => 'A espécie é obrigatória.',
            'especie.in' => 'A espécie selecionada não é válida.',
            'quantidade.required' => 'A quantidade é obrigatória.',
            'quantidade.integer' => 'A quantidade deve ser um número inteiro.',
            'quantidade.min' => 'A quantidade deve ser pelo menos 1.',
            'quantidade.max' => 'A quantidade não pode ser maior que 999.999.',
            'finalidade.required' => 'A finalidade é obrigatória.',
            'finalidade.in' => 'A finalidade selecionada não é válida.',
            'data_atualizacao.date' => 'A data de atualização deve ser uma data válida.',
            'propriedade_id.required' => 'A propriedade é obrigatória.',
            'propriedade_id.exists' => 'A propriedade selecionada não existe.'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'data_atualizacao' => $this->data_atualizacao ?? now()
        ]);
    }
}
