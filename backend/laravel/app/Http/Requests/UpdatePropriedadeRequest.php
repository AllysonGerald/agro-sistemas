<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropriedadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cep' => ['nullable', 'string', 'max:10'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'municipio' => ['required', 'string', 'max:255'],
            'uf' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'inscricao_estadual' => ['nullable', 'string', 'max:50'],
            'car' => ['nullable', 'string', 'max:50'],
            'matricula' => ['nullable', 'string', 'max:50'],
            'cartorio' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'area_total' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'area_preservada' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'tipo_exploracao' => ['nullable', 'in:pecuaria,agricultura,mista,silvicultura,outro'],
            'data_aquisicao' => ['nullable', 'date'],
            'observacoes' => ['nullable', 'string'],
            'produtor_id' => ['required', 'exists:produtores_rurais,id']
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome da propriedade é obrigatório.',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            'municipio.required' => 'O município é obrigatório.',
            'municipio.max' => 'O município não pode ter mais de 255 caracteres.',
            'uf.required' => 'A UF é obrigatória.',
            'uf.size' => 'A UF deve ter exatamente 2 caracteres.',
            'uf.regex' => 'A UF deve conter apenas letras maiúsculas.',
            'inscricao_estadual.max' => 'A inscrição estadual não pode ter mais de 50 caracteres.',
            'area_total.required' => 'A área total é obrigatória.',
            'area_total.numeric' => 'A área total deve ser um número.',
            'area_total.min' => 'A área total deve ser maior que zero.',
            'area_total.max' => 'A área total não pode ser maior que 999.999,99 hectares.',
            'produtor_id.required' => 'O produtor é obrigatório.',
            'produtor_id.exists' => 'O produtor selecionado não existe.'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'uf' => strtoupper($this->uf ?? '')
        ]);
    }
}
