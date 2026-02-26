<?php

namespace Tecnozt\Proteczt;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Logging\Log as LogContract;
use Psr\Log\LoggerInterface;

class ProtecztLicenseService
{
    protected array $config;
    protected CacheContract $cache;
    protected LoggerInterface $logger;

    public function __construct(array $config, CacheContract $cache, LoggerInterface $logger)
    {
        $this->config  = $config;
        $this->cache   = $cache;
        $this->logger  = $logger;
    }

    /**
     * Register client application to Proteczt server.
     */
    public function register(): bool
    {
        $domain = $this->getDomain();

        try {
            $response = $this->makeClient()->post('/api/clients', [
                'json' => [
                    'app_name'  => config('app.name'),
                    'domain'    => $domain,
                    'host_name' => gethostname() ?: $domain,
                    'ip_address' => $this->getServerIp(),
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200 || $statusCode === 201) {
                $this->logger->info('[Proteczt] Client registered successfully.', [
                    'domain' => $domain,
                ]);
                return true;
            }

            $this->logger->warning('[Proteczt] Registration returned unexpected status.', [
                'status' => $statusCode,
            ]);

            return false;

        } catch (ConnectException $e) {
            $this->logger->warning('[Proteczt] Cannot connect to server for registration.', [
                'error' => $e->getMessage(),
            ]);
            return false;
        } catch (\Throwable $e) {
            $this->logger->error('[Proteczt] Registration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check license status, with caching.
     * Returns ['active' => bool, 'data' => array|null]
     */
    public function checkStatus(): array
    {
        $cacheKey      = $this->buildCacheKey();
        $cacheDuration = (int) ($this->config['cache_duration'] ?? 3600);

        return $this->cache->remember($cacheKey, $cacheDuration, function () {
            return $this->fetchStatusFromServer();
        });
    }

    /**
     * Force-refresh license status (bypass cache).
     */
    public function refreshStatus(): array
    {
        $this->cache->forget($this->buildCacheKey());
        return $this->fetchStatusFromServer();
    }

    /**
     * Check if license is currently active.
     */
    public function isActive(): bool
    {
        // Skip check in local/testing environment if configured
        if ($this->shouldSkip()) {
            return true;
        }

        $status = $this->checkStatus();
        return (bool) ($status['active'] ?? true); // fail-open
    }

    /**
     * Fetch license status directly from Proteczt server.
     */
    protected function fetchStatusFromServer(): array
    {
        $domain = $this->getDomain();

        try {
            $response = $this->makeClient()->post('/api/clients/check-status', [
                'json' => [
                    'domain' => $domain,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body       = json_decode((string) $response->getBody(), true);

            if ($statusCode === 200 && is_array($body)) {
                $this->logger->info('[Proteczt] License check successful.', [
                    'domain' => $domain,
                    'active' => $body['active'] ?? false,
                ]);

                return [
                    'active' => (bool) ($body['active'] ?? false),
                    'data'   => $body['data'] ?? null,
                ];
            }

            $this->logger->warning('[Proteczt] Unexpected response from server.', [
                'status' => $statusCode,
            ]);

            // Fail-open: allow access if server returns unexpected response
            return $this->failOpen('unexpected_response');

        } catch (ConnectException $e) {
            // Server unreachable — fail-open (do not block client app)
            $this->logger->warning('[Proteczt] Cannot reach server: ' . $e->getMessage());
            return $this->failOpen('connection_error');

        } catch (RequestException $e) {
            $statusCode = $e->getResponse()?->getStatusCode();

            // 401 or 403 means token is invalid → block access
            if (in_array($statusCode, [401, 403])) {
                $this->logger->error('[Proteczt] Authentication failed. Check PROTECZT_API_TOKEN.');
                return ['active' => false, 'data' => null];
            }

            $this->logger->error('[Proteczt] Request error: ' . $e->getMessage());
            return $this->failOpen('request_error');

        } catch (\Throwable $e) {
            $this->logger->error('[Proteczt] Unexpected error: ' . $e->getMessage());
            return $this->failOpen('unknown_error');
        }
    }

    /**
     * Build a stable, opaque cache key (hashed to avoid exposing domain).
     */
    protected function buildCacheKey(): string
    {
        $domain = $this->getDomain();
        return 'proteczt_' . hash('sha256', $domain . ($this->config['api_token'] ?? ''));
    }

    /**
     * Determine the domain to identify this client.
     */
    protected function getDomain(): string
    {
        $domain = $this->config['domain'] ?? null;

        if (empty($domain)) {
            $domain = request()->getHost();
        }

        return strtolower(trim($domain));
    }

    /**
     * Get server IP address (for registration metadata).
     */
    protected function getServerIp(): string
    {
        try {
            return request()->server('SERVER_ADDR') ?? gethostbyname(gethostname()) ?? '0.0.0.0';
        } catch (\Throwable) {
            return '0.0.0.0';
        }
    }

    /**
     * Return a fail-open result (allow access).
     */
    protected function failOpen(string $reason): array
    {
        return [
            'active'       => true,
            'data'         => null,
            'fail_open'    => true,
            'fail_reason'  => $reason,
        ];
    }

    /**
     * Determine if the license check should be skipped for this environment.
     */
    protected function shouldSkip(): bool
    {
        if (!($this->config['skip_local'] ?? true)) {
            return false;
        }

        return app()->environment(['local', 'testing']);
    }

    /**
     * Build a pre-configured Guzzle HTTP client.
     */
    protected function makeClient(): Client
    {
        $apiUrl  = rtrim($this->config['api_url'] ?? '', '/');
        $token   = $this->config['api_token'] ?? '';
        $timeout = (int) ($this->config['timeout'] ?? 10);

        if (empty($apiUrl)) {
            throw new \RuntimeException('[Proteczt] PROTECZT_API_URL is not configured.');
        }

        if (empty($token)) {
            throw new \RuntimeException('[Proteczt] PROTECZT_API_TOKEN is not configured.');
        }

        return new Client([
            'base_uri'    => $apiUrl,
            'timeout'     => $timeout,
            'http_errors' => true,
            'headers'     => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'X-Proteczt-Client-Version' => '1.0.0',
            ],
            'verify' => $this->config['verify_ssl'] ?? true,
        ]);
    }
}
