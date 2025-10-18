<?php

use App\Services\PasswordResetService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tokens:clean {--dry-run : Executar em modo de teste sem deletar} {--force : Forçar limpeza mesmo se houver muitos tokens}', function (PasswordResetService $passwordResetService) {
    $this->info('🧹 Iniciando limpeza de tokens expirados...');

    if ($this->option('dry-run')) {
        $this->warn('⚠️  MODO DE TESTE - Nenhum token será deletado');
    }

    try {
        // Contar tokens expirados antes da limpeza
        $expiredCount = DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subMinutes(PasswordResetService::TOKEN_EXPIRE_MINUTES))
            ->count();

        if ($expiredCount === 0) {
            $this->info('✅ Nenhum token expirado encontrado');
            return 0;
        }

        $this->info("📊 Tokens expirados encontrados: {$expiredCount}");

        // Verificar se há muitos tokens para confirmar
        if ($expiredCount > 100 && !$this->option('force') && !$this->option('dry-run')) {
            if (!$this->confirm("Você tem certeza que deseja deletar {$expiredCount} tokens?")) {
                $this->info('❌ Operação cancelada pelo usuário');
                return 1;
            }
        }

        if (!$this->option('dry-run')) {
            $deletedCount = $passwordResetService->cleanExpiredTokens();

            $this->info("🗑️  Tokens deletados: {$deletedCount}");
            $this->info('✅ Limpeza concluída com sucesso!');
        } else {
            $this->info("📋 Tokens que seriam deletados: {$expiredCount}");
        }

        return 0;

    } catch (\Exception $e) {
        $this->error('❌ Erro durante a limpeza: ' . $e->getMessage());
        return 1;
    }
})->purpose('Limpar tokens de recuperação de senha expirados');
