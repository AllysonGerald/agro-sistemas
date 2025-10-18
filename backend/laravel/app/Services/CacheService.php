<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Serviço de Cache Empresarial
 *
 * Implementa arquitetura de cache distribuído com estratégias adaptativas de TTL.
 * Desenvolvido para maximizar performance em ambientes de alta demanda.
 *
 * @package App\Services
 * @author Sistema Agropecuário
 * @version 1.0.0
 */
class CacheService
{
    /**
     * Matriz de configuração TTL otimizada para diferentes padrões de acesso
     * Baseada em análise de comportamento de dados do setor agropecuário
     */
    const CACHE_TIMES = [
        'short' => 5,           // Dados transacionais em tempo real
        'medium' => 30,         // Consultas operacionais dinâmicas
        'long' => 120,          // Dados de configuração e mestres
        'daily' => 1440,        // Relatórios consolidados diários
        'weekly' => 10080,      // Dados históricos e estatísticas
    ];

    /**
     * Prefixos de cache para diferentes módulos
     */
    const CACHE_PREFIXES = [
        'produtor' => 'prod',
        'propriedade' => 'prop',
        'unidade' => 'unid',
        'rebanho' => 'reb',
        'relatorio' => 'rel',
        'dashboard' => 'dash',
        'auth' => 'auth',
    ];

    /**
     * Gerador de chaves de cache inteligente e padronizado
     *
     * Implementa algoritmo determinístico para garantir consistência
     * e evitar colisões em ambientes distribuídos
     */
    public static function generateKey(string $prefix, string $identifier, array $params = []): string
    {
        $baseKey = self::CACHE_PREFIXES[$prefix] ?? $prefix;
        $paramString = empty($params) ? '' : '_' . md5(serialize($params));

        return "agro_{$baseKey}_{$identifier}{$paramString}";
    }

    /**
     * Persistir dados no cache com estratégia TTL otimizada
     *
     * Implementa mecanismo de fallback e logging para auditoria
     */
    public static function put(string $key, $data, string $duration = 'medium'): bool
    {
        try {
            $minutes = self::CACHE_TIMES[$duration] ?? $duration;
            $success = Cache::put($key, [
                'data' => $data,
                'cached_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addMinutes($minutes)
            ], $minutes);

            if ($success) {
                Log::info("Cache armazenado: {$key} por {$minutes} minutos");
            }

            return $success;
        } catch (\Exception $e) {
            Log::error("Erro ao armazenar cache {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Recuperar dados do cache
     */
    public static function get(string $key, $default = null)
    {
        try {
            $cached = Cache::get($key);

            if ($cached && is_array($cached) && isset($cached['data'])) {
                Log::info("Cache hit: {$key}");
                return $cached['data'];
            }

            Log::info("Cache miss: {$key}");
            return $default;
        } catch (\Exception $e) {
            Log::error("Erro ao recuperar cache {$key}: " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Executar closure com cache automático
     */
    public static function remember(string $key, \Closure $callback, string $duration = 'medium')
    {
        try {
            $cached = self::get($key);

            if ($cached !== null) {
                return $cached;
            }

            // Executar callback e armazenar resultado
            $result = $callback();
            self::put($key, $result, $duration);

            return $result;
        } catch (\Exception $e) {
            Log::error("Erro no cache remember {$key}: " . $e->getMessage());
            return $callback();
        }
    }

    /**
     * Invalidar cache específico
     */
    public static function forget(string $key): bool
    {
        try {
            $success = Cache::forget($key);
            if ($success) {
                Log::info("Cache invalidado: {$key}");
            }
            return $success;
        } catch (\Exception $e) {
            Log::error("Erro ao invalidar cache {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidar múltiplas chaves de cache
     */
    public static function forgetMany(array $keys): array
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = self::forget($key);
        }
        return $results;
    }

    /**
     * Invalidar cache por padrão (usando tags se disponível)
     */
    public static function forgetByPattern(string $pattern): bool
    {
        try {
            // Para Redis, podemos usar padrões
            if (config('cache.default') === 'redis') {
                $keys = Cache::getRedis()->keys("*{$pattern}*");
                foreach ($keys as $key) {
                    Cache::forget(str_replace(config('cache.prefix'), '', $key));
                }
                Log::info("Cache pattern invalidado: {$pattern}");
                return true;
            }

            // Fallback: limpar todo o cache
            return Cache::flush();
        } catch (\Exception $e) {
            Log::error("Erro ao invalidar cache por padrão {$pattern}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidar cache de um módulo inteiro
     */
    public static function forgetModule(string $module): bool
    {
        $prefix = self::CACHE_PREFIXES[$module] ?? $module;
        return self::forgetByPattern("agro_{$prefix}_");
    }

    /**
     * Obter estatísticas do cache
     */
    public static function getStats(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $info = $redis->info();

                return [
                    'driver' => 'redis',
                    'memory_used' => $info['used_memory_human'] ?? 'N/A',
                    'connected_clients' => $info['connected_clients'] ?? 'N/A',
                    'total_commands' => $info['total_commands_processed'] ?? 'N/A',
                    'keyspace_hits' => $info['keyspace_hits'] ?? 'N/A',
                    'keyspace_misses' => $info['keyspace_misses'] ?? 'N/A',
                    'hit_rate' => isset($info['keyspace_hits'], $info['keyspace_misses'])
                        ? round($info['keyspace_hits'] / ($info['keyspace_hits'] + $info['keyspace_misses']) * 100, 2) . '%'
                        : 'N/A'
                ];
            }

            return [
                'driver' => config('cache.default'),
                'message' => 'Estatísticas não disponíveis para este driver'
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Limpar todo o cache
     */
    public static function flush(): bool
    {
        try {
            $success = Cache::flush();
            if ($success) {
                Log::info("Todo o cache foi limpo");
            }
            return $success;
        } catch (\Exception $e) {
            Log::error("Erro ao limpar cache: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se uma chave existe no cache
     */
    public static function has(string $key): bool
    {
        try {
            return Cache::has($key);
        } catch (\Exception $e) {
            Log::error("Erro ao verificar cache {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Renovar TTL de uma chave existente
     */
    public static function refresh(string $key, string $duration = 'medium'): bool
    {
        try {
            $data = self::get($key);
            if ($data !== null) {
                return self::put($key, $data, $duration);
            }
            return false;
        } catch (\Exception $e) {
            Log::error("Erro ao renovar cache {$key}: " . $e->getMessage());
            return false;
        }
    }
}
