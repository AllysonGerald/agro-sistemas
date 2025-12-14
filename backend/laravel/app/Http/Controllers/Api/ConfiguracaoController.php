<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfiguracaoController extends Controller
{
    /**
     * Retorna as configurações do usuário autenticado
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();

            $configuracao = Configuracao::where('user_id', $user->id)->first();

            // Se não existir, criar configurações padrão
            if (!$configuracao) {
                $configuracao = Configuracao::criarPadrao($user->id);
            }

            return response()->json([
                'success' => true,
                'data' => $configuracao
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar configurações',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza as configurações do sistema
     */
    public function updateSistema(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tema' => 'required|in:claro,escuro,auto',
                'idioma' => 'required|in:pt-BR,en,es',
                'formato_data' => 'required|in:DD/MM/YYYY,MM/DD/YYYY,YYYY-MM-DD',
                'moeda' => 'required|in:BRL,USD,EUR',
            ]);

            $user = Auth::user();
            $configuracao = Configuracao::where('user_id', $user->id)->first();

            if (!$configuracao) {
                $configuracao = Configuracao::criarPadrao($user->id);
            }

            $configuracao->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Preferências atualizadas com sucesso',
                'data' => $configuracao
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
                'message' => 'Erro ao atualizar preferências',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza as configurações de notificações
     */
    public function updateNotificacoes(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'notificacao_email' => 'required|boolean',
                'notificacao_estoque' => 'required|boolean',
                'notificacao_manejo' => 'required|boolean',
                'notificacao_financeiro' => 'required|boolean',
            ]);

            $user = Auth::user();
            $configuracao = Configuracao::where('user_id', $user->id)->first();

            if (!$configuracao) {
                $configuracao = Configuracao::criarPadrao($user->id);
            }

            $configuracao->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Configurações de notificação atualizadas',
                'data' => $configuracao
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
                'message' => 'Erro ao atualizar notificações',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

