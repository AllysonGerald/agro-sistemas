<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    /**
     * Retorna o perfil do usuário autenticado
     */
    public function show(): JsonResponse
    {
        try {
            $user = Auth::user();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza o perfil do usuário
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
            ]);

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
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
                'message' => 'Erro ao atualizar perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Altera a senha do usuário
     */
    public function alterarSenha(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'senha_atual' => 'required|string',
                'senha_nova' => 'required|string|min:8|confirmed',
            ]);

            // Verificar se a senha atual está correta
            if (!Hash::check($validated['senha_atual'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Senha atual incorreta'
                ], 422);
            }

            // Atualizar a senha
            $user->update([
                'password' => Hash::make($validated['senha_nova'])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Senha alterada com sucesso'
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
                'message' => 'Erro ao alterar senha',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

