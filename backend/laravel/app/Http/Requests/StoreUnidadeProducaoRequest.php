<?php

namespace App\Http\Requests;

use App\Enums\CropTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnidadeProducaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome_cultura' => [
                'required',
                'string',
                Rule::in(array_column(CropTypeEnum::cases(), 'value'))
            ],
            'area_total_ha' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'coordenadas_geograficas' => ['nullable', 'array'],
            'coordenadas_geograficas.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'coordenadas_geograficas.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'propriedade_id' => ['required', 'exists:propriedades,id']
        ];
    }

    public function messages(): array
    {
        return [
            'nome_cultura.required' => 'O nome da cultura é obrigatório.',
            'nome_cultura.in' => 'A cultura selecionada não é válida.',
            'area_total_ha.required' => 'A área total é obrigatória.',
            'area_total_ha.numeric' => 'A área total deve ser um número.',
            'area_total_ha.min' => 'A área total deve ser maior que zero.',
            'area_total_ha.max' => 'A área total não pode ser maior que 99.999,99 hectares.',
            'coordenadas_geograficas.array' => 'As coordenadas devem ser um objeto válido.',
            'coordenadas_geograficas.lat.numeric' => 'A latitude deve ser um número.',
            'coordenadas_geograficas.lat.between' => 'A latitude deve estar entre -90 e 90 graus.',
            'coordenadas_geograficas.lng.numeric' => 'A longitude deve ser um número.',
            'coordenadas_geograficas.lng.between' => 'A longitude deve estar entre -180 e 180 graus.',
            'propriedade_id.required' => 'A propriedade é obrigatória.',
            'propriedade_id.exists' => 'A propriedade selecionada não existe.'
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('propriedade_id')) {
                $propriedade = \App\Models\Propriedade::find($this->propriedade_id);
                if ($propriedade && $this->area_total_ha > $propriedade->area_total) {
                    $validator->errors()->add(
                        'area_total_ha',
                        'A área da unidade de produção não pode ser maior que a área total da propriedade.'
                    );
                }
            }
        });
    }
}
