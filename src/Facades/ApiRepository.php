<?php

namespace Mohib\LaravelApiRepository\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $endpoint, array $params = [], string $cacheKey = null, int $ttl = 600)
 * @method static mixed post(string $endpoint, array $data = [])
 * @method static mixed put(string $endpoint, array $data = [])
 * @method static mixed delete(string $endpoint)
 * @method static void clearCache($key)
 *
 * @see \Mohib\LaravelApiRepository\BaseApiRepository
 */
class ApiRepository extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'api-repository';
    }
}
