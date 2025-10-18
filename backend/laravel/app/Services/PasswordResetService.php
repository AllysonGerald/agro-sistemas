<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Serviço de Recuperação de Senha
 *
 * Implementa sistema seguro de reset de senha com tokens temporários
 * e notificações por email para o setor agropecuário.
 *
 * @package App\Services
 * @author Sistema Agropecuário
 * @version 1.0.0
 */
class PasswordResetService
{
    /**
     * Configurações de segurança para tokens
     */
    const TOKEN_EXPIRE_MINUTES = 60; // 1 hora
    const TOKEN_LENGTH = 64;

    /**
     * Gerar e enviar token de recuperação de senha
     *
     * Cria token seguro e envia instruções por email
     */
    public function sendResetLink(string $email): bool
    {
        try {
            // Verificar se usuário existe
            $user = User::where('email', $email)->first();
            if (!$user) {
                return false;
            }

            // Remover tokens existentes para este email
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();

            // Gerar novo token seguro
            $token = Str::random(self::TOKEN_LENGTH);

            // Armazenar token no banco
            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]);

            // Enviar email com token (simulação por enquanto)
            $this->sendResetEmail($user, $token);

            return true;

        } catch (\Exception $e) {
            Log::error('Erro ao enviar link de recuperação: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validar token e resetar senha
     *
     * Verifica token e atualiza senha do usuário
     */
    public function resetPassword(string $email, string $token, string $newPassword): array
    {
        try {
            // Buscar token no banco
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->first();

            if (!$resetRecord) {
                return [
                    'success' => false,
                    'message' => 'Token inválido ou expirado'
                ];
            }

            // Verificar se token não expirou
            $tokenAge = Carbon::parse($resetRecord->created_at)->diffInMinutes(Carbon::now());
            if ($tokenAge > self::TOKEN_EXPIRE_MINUTES) {
                // Remover token expirado
                DB::table('password_reset_tokens')->where('email', $email)->delete();

                return [
                    'success' => false,
                    'message' => 'Token expirado. Solicite um novo link de recuperação'
                ];
            }

            // Verificar se token é válido
            if (!Hash::check($token, $resetRecord->token)) {
                return [
                    'success' => false,
                    'message' => 'Token inválido'
                ];
            }

            // Atualizar senha do usuário
            $user = User::where('email', $email)->first();
            $user->password = Hash::make($newPassword);
            $user->save();

            // Remover token usado
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            // Revogar todas as sessões ativas do usuário
            $user->tokens()->delete();

            return [
                'success' => true,
                'message' => 'Senha alterada com sucesso'
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao resetar senha: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor'
            ];
        }
    }

    /**
     * Verificar se token é válido (para validação frontend)
     */
    public function validateToken(string $email, string $token): bool
    {
        try {
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->first();

            if (!$resetRecord) {
                return false;
            }

            // Verificar expiração
            $tokenAge = Carbon::parse($resetRecord->created_at)->diffInMinutes(Carbon::now());
            if ($tokenAge > self::TOKEN_EXPIRE_MINUTES) {
                DB::table('password_reset_tokens')->where('email', $email)->delete();
                return false;
            }

            // Verificar token
            return Hash::check($token, $resetRecord->token);

        } catch (\Exception $e) {
            Log::error('Erro ao validar token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Simular envio de email (implementar com provedor real)
     */
    private function sendResetEmail(User $user, string $token): void
    {
        // Por enquanto apenas log - implementar com Mail ou provedor externo
        Log::info("Token de recuperação para {$user->email}: {$token}");

        // Exemplo de implementação real:
        /*
        Mail::send('emails.password-reset', [
            'user' => $user,
            'token' => $token,
            'resetUrl' => config('app.frontend_url') . "/reset-password?token={$token}&email={$user->email}"
        ], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Recuperação de Senha - Sistema Agropecuário');
        });
        */
    }

    /**
     * Limpar tokens expirados (para comando scheduled)
     */
    public function cleanExpiredTokens(): int
    {
        $expiredTime = Carbon::now()->subMinutes(self::TOKEN_EXPIRE_MINUTES);

        return DB::table('password_reset_tokens')
            ->where('created_at', '<', $expiredTime)
            ->delete();
    }
}
