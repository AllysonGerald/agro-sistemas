<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PasswordResetService;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ValidateResetTokenRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

/**
 * @tags Authentication
 */
class AuthController extends Controller
{
    public function __construct(
        private PasswordResetService $passwordResetService
    ) {}

    /**
     * Registrar novo usuário
     *
     * Cria uma nova conta de usuário no sistema
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Usuário registrado com sucesso",
     *   "data": {
     *     "access_token": "1|token...",
     *     "token_type": "Bearer",
     *     "user": {
     *       "id": 1,
     *       "name": "João Silva",
     *       "email": "joao@agrosistemas.com",
     *       "created_at": "2025-01-01T00:00:00.000000Z"
     *     }
     *   }
     * }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Usuário registrado com sucesso',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    /**
     * Login do usuário
     *
     * Autentica um usuário e retorna um token de acesso
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Login realizado com sucesso",
     *   "data": {
     *     "access_token": "1|token...",
     *     "token_type": "Bearer",
     *     "user": {
     *       "id": 1,
     *       "name": "João Silva",
     *       "email": "joao@agrosistemas.com"
     *     }
     *   }
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "message": "Credenciais inválidas"
     * }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Logout do usuário
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    /**
     * Obter dados do usuário autenticado
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    /**
     * Revogar todos os tokens do usuário
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Todos os tokens foram revogados'
        ]);
    }

    /**
     * Solicitar recuperação de senha
     *
     * Envia email com token para reset de senha
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $success = $this->passwordResetService->sendResetLink($request->email);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Instruções de recuperação enviadas para seu email'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao enviar email de recuperação'
        ], 500);
    }

    /**
     * Validar token de recuperação
     *
     * Verifica se token é válido antes do reset
     */
    public function validateResetToken(ValidateResetTokenRequest $request): JsonResponse
    {
        $isValid = $this->passwordResetService->validateToken(
            $request->email,
            $request->token
        );

        return response()->json([
            'success' => $isValid,
            'message' => $isValid ? 'Token válido' : 'Token inválido ou expirado'
        ]);
    }

    /**
     * Resetar senha com token
     *
     * Altera senha do usuário usando token de recuperação
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->passwordResetService->resetPassword(
            $request->email,
            $request->token,
            $request->password
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
