<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProdutorRuralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['sometimes', 'string', 'max:255'],
            'cpf_cnpj' => [
                'sometimes',
                'string',
                'regex:/^(\d{11}|\d{14})$/',
                Rule::unique('produtores_rurais', 'cpf_cnpj')->ignore($this->route('id'))
            ],
            'telefone' => ['sometimes', 'string', 'regex:/^\d{10,11}$/'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('produtores_rurais', 'email')->ignore($this->route('id'))
            ],
            'endereco' => ['sometimes', 'string', 'max:500'],
            'data_cadastro' => ['sometimes', 'date']
        ];
    }

    public function messages(): array
    {
        return [
            'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            'cpf_cnpj.regex' => 'O CPF/CNPJ deve conter 11 ou 14 dígitos.',
            'cpf_cnpj.unique' => 'Este CPF/CNPJ já está cadastrado.',
            'telefone.regex' => 'O telefone deve conter 10 ou 11 dígitos.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.unique' => 'Este email já está cadastrado.',
            'endereco.max' => 'O endereço não pode ter mais de 500 caracteres.',
            'data_cadastro.date' => 'A data de cadastro deve ser uma data válida.'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf_cnpj' => preg_replace('/\D/', '', $this->cpf_cnpj ?? ''),
            'telefone' => preg_replace('/\D/', '', $this->telefone ?? '')
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
