<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * A minimal GitHub App client for one repository.
 */
class GitHub
{
    /**
     * The GitHub REST API base URL.
     */
    protected const string API = 'https://api.github.com';

    /**
     * Determine if the GitHub App is configured.
     */
    public function configured(): bool
    {
        return filled(config('services.github.app_id'))
            && filled(config('services.github.installation_id'))
            && filled(config('services.github.repository'))
            && filled($this->privateKey());
    }

    /**
     * Get the repository the issues are created in, as "owner/name".
     */
    public function repository(): string
    {
        return (string) config('services.github.repository');
    }

    /**
     * Create an issue in the repository.
     *
     * @param  array<int, string>  $labels
     * @return array{number: int, html_url: string}
     */
    public function createIssue(string $title, string $body, array $labels = []): array
    {
        $response = $this->request()
            ->post("/repos/{$this->repository()}/issues", array_filter([
                'title' => $title,
                'body' => $body,
                'labels' => $labels,
            ]))
            ->throw();

        return ['number' => (int) $response->json('number'), 'html_url' => (string) $response->json('html_url')];
    }

    /**
     * Determine if the webhook payload signature is valid.
     */
    public function validSignature(string $payload, string|null $signature): bool
    {
        $secret = (string) config('services.github.webhook_secret');

        return $secret !== ''
            && is_string($signature)
            && hash_equals('sha256=' . hash_hmac('sha256', $payload, $secret), $signature);
    }

    /**
     * Get an authenticated request for the installation.
     */
    protected function request(): PendingRequest
    {
        return Http::baseUrl(self::API)
            ->withToken($this->installationToken())
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->acceptJson()
            ->timeout(15);
    }

    /**
     * Get an installation access token, cached until shortly before it expires.
     */
    protected function installationToken(): string
    {
        $installationId = config('services.github.installation_id');

        return Cache::remember("github.installation_token.{$installationId}", now()->addMinutes(50), function () use ($installationId) {
            return Http::baseUrl(self::API)
                ->withToken($this->appJwt())
                ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
                ->acceptJson()
                ->post("/app/installations/{$installationId}/access_tokens")
                ->throw()
                ->json('token');
        });
    }

    /**
     * Create the short-lived JWT that authenticates as the GitHub App.
     */
    protected function appJwt(): string
    {
        $encode = fn (string $value) => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');

        $now = time();
        $unsigned = $encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']))
            . '.' . $encode(json_encode(['iat' => $now - 60, 'exp' => $now + 540, 'iss' => (string) config('services.github.app_id')]));

        $key = openssl_pkey_get_private($this->privateKey());

        if ($key === false || !openssl_sign($unsigned, $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('The GitHub App private key is invalid.');
        }

        return $unsigned . '.' . $encode($signature);
    }

    /**
     * Get the private key, from GITHUB_APP_PRIVATE_KEY or the file at GITHUB_APP_PRIVATE_KEY_PATH.
     */
    protected function privateKey(): string
    {
        $key = (string) config('services.github.private_key');

        if ($key === '' && filled($path = config('services.github.private_key_path'))) {
            $path = str_starts_with($path, '/') ? $path : base_path($path);
            $key = is_readable($path) ? (string) file_get_contents($path) : '';
        }

        return str_replace('\n', "\n", $key);
    }
}
