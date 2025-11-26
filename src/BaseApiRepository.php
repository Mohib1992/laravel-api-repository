<?php

namespace Mohib\LaravelApiRepository;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

abstract class BaseApiRepository
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('laravel-api-repository.api.base_url', 'https://default-api-url.com');
    }

    protected function request($method, $endpoint, $data = [], $cacheKey = null, $ttl = 600)
    {
        // Apply Rate Limiting
        $rateLimitKey = 'api_rate_limit:' . $endpoint;
        if (!RateLimiter::attempt($rateLimitKey, 10, function () {})) { // 10 requests per minute
            Log::warning("Rate limit exceeded for endpoint: $endpoint");
            return ['error' => 'Rate limit exceeded', 'status' => 429];
        }

        // Handle Caching for GET requests
        if ($method === 'get' && $cacheKey) {
            return Cache::remember($cacheKey, $ttl, function () use ($method, $endpoint, $data) {
                return $this->fetchFromApi($method, $endpoint, $data);
            });
        }

        return $this->fetchFromApi($method, $endpoint, $data);
    }

    private function fetchFromApi($method, $endpoint, $data)
    {
        try {
            $response = Http::withHeaders($this->headers())->{$method}($this->baseUrl . $endpoint, $data);

            if ($response->successful()) {
                Log::info("API Request Successful: $method $endpoint", ['response' => $response->json()]);
                return $response->json();
            }

            Log::error("API Request Failed: $method $endpoint", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => 'API request failed', 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error("API Request Exception: $method $endpoint", ['error' => $e->getMessage()]);
            return ['error' => 'Exception occurred', 'status' => 500];
        }
    }

    public function get(string $endpoint, array $params = [], string $cacheKey = null, int $ttl = 600)
    {
        return $this->request('get', $endpoint, $params, $cacheKey, $ttl);
    }

    public function post(string $endpoint, array $data = [])
    {
        return $this->request('post', $endpoint, $data);
    }

    public function put(string $endpoint, array $data = [])
    {
        return $this->request('put', $endpoint, $data);
    }

    public function delete(string $endpoint)
    {
        return $this->request('delete', $endpoint);
    }

    public function clearCache($key): void
    {
        Cache::forget($key);
    }

    abstract protected function headers();

}
