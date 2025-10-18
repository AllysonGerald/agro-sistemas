<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * @bodyParam nome string required Nome completo do produtor rural. Example: João Silva Santos
 * @bodyParam cpf_cnpj string required CPF (11 dígitos) ou CNPJ (14 dígitos) apenas números. Example: 12345678900
 * @bodyParam telefone string required Telefone com 10 ou 11 dígitos. Example: 85999999999
 * @bodyParam email string required Email válido do produtor. Example: joao.silva@fazenda.com
 * @bodyParam endereco string required Endereço completo. Example: Zona Rural, Sobral/CE
 */
class StoreProdutorRuralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpf_cnpj' => [
                'required',
                'string',
                'regex:/^(\d{11}|\d{14})$/',
                Rule::unique('produtores_rurais', 'cpf_cnpj')
            ],
            'telefone' => ['required', 'string', 'regex:/^\d{10,11}$/'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('produtores_rurais', 'email')
            ],
            'endereco' => ['required', 'string', 'max:500'],
            'data_cadastro' => ['nullable', 'date']
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            'cpf_cnpj.required' => 'O CPF/CNPJ é obrigatório.',
            'cpf_cnpj.regex' => 'O CPF/CNPJ deve conter 11 ou 14 dígitos.',
            'cpf_cnpj.unique' => 'Este CPF/CNPJ já está cadastrado.',
            'telefone.required' => 'O telefone é obrigatório.',
            'telefone.regex' => 'O telefone deve conter 10 ou 11 dígitos.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.unique' => 'Este email já está cadastrado.',
            'endereco.required' => 'O endereço é obrigatório.',
            'endereco.max' => 'O endereço não pode ter mais de 500 caracteres.',
            'data_cadastro.date' => 'A data de cadastro deve ser uma data válida.'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf_cnpj' => preg_replace('/\D/', '', $this->cpf_cnpj ?? ''),
            'telefone' => preg_replace('/\D/', '', $this->telefone ?? ''),
            'data_cadastro' => $this->data_cadastro ?? now()
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Dados de validação inválidos',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
