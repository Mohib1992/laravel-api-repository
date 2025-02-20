<?php
namespace Mohib\LaravelApiRepository\Test;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use Mohib\LaravelApiRepository\BaseApiRepository;
use Orchestra\Testbench\TestCase;

class BaseApiRepositoryTest extends TestCase
{
    protected $repository;

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('laravel-api-repository.api.base_url', 'https://api.example.com');
    }

    public function setUp(): void {
        parent::setUp();

        // Create a Mocked instance of the BaseApiRepository
        $this->repository = Mockery::mock(BaseApiRepository::class)->makePartial();

        // Mock required abstract methods
        $this->repository->shouldAllowMockingProtectedMethods();
        $this->repository->shouldReceive('headers')->andReturn(['Authorization' => 'Bearer test_token']);
        $this->repository->shouldReceive('authenticate');
    }

    /** @tests */
    public function it_makes_a_successful_get_request(): void
    {
        Http::fake([
            'https://api.example.com/test-endpoint' => Http::response(['data' => 'Test Response'], 200),
        ]);

        $response = $this->repository->get('/tests-endpoint');

        $this->assertEquals(['data' => 'Test Response'], $response);
    }

    /** @tests */
    public function it_handles_api_failure(): void
    {
        Http::fake([
            'https://api.example.com/fail-endpoint' => Http::response(['error' => 'Failed'], 500),
        ]);

        $response = $this->repository->get('/fail-endpoint');

        $this->assertEquals(['error' => 'API request failed', 'status' => 500], $response);
    }

    /** @tests */
    public function it_handles_rate_limiting(): void
    {
        RateLimiter::shouldReceive('attempt')->once()->andReturn(false);

        Log::shouldReceive('warning')->once()->with('Rate limit exceeded for endpoint: /rate-limited');

        $response = $this->repository->get('/rate-limited');

        $this->assertEquals(['error' => 'Rate limit exceeded', 'status' => 429], $response);
    }

    /** @tests */
    public function it_caches_get_requests(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(['data' => 'Cached Response']);

        $response = $this->repository->get('/cached-endpoint', [], 'cache_key');

        $this->assertEquals(['data' => 'Cached Response'], $response);
    }

    /** @tests */
    public function it_clears_cache(): void
    {
        Cache::shouldReceive('forget')->once()->with('cache_key');

        $this->repository->clearCache('cache_key');

        $this->assertTrue(true); // Ensure the method executes without errors
    }
}
