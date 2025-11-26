<?php

namespace Mohib\LaravelApiRepository;

class ApiRepository extends BaseApiRepository
{
    protected function headers()
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function authenticate()
    {
        // Default authentication implementation (if needed in future)
    }
}
