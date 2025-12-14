<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    /**
     * Retorna os dados da empresa
     */
    public function show(): JsonResponse
    {
        try {
            $empresa = Empresa::first();

            if (!$empresa) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Nenhuma empresa cadastrada'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $empresa
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados da empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cria ou atualiza os dados da empresa
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'razao_social' => 'required|string|max:255',
                'nome_fantasia' => 'nullable|string|max:255',
                'cnpj' => 'required|string|max:18',
                'inscricao_estadual' => 'nullable|string|max:20',
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email',
                'endereco' => 'nullable|string',
                'cep' => 'nullable|string|max:10',
                'cidade' => 'nullable|string|max:100',
                'estado' => 'nullable|string|size:2',
            ]);

            $empresa = Empresa::first();

            if ($empresa) {
                $empresa->update($validated);
                $message = 'Dados da empresa atualizados com sucesso';
            } else {
                $empresa = Empresa::create($validated);
                $message = 'Empresa cadastrada com sucesso';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $empresa
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar dados da empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

