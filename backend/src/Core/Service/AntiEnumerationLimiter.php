<?php

namespace App\Core\Service;

use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

readonly class AntiEnumerationLimiter
{
    public function __construct(
        #[Target('anti_enumeration')]
        private RateLimiterFactoryInterface $rateLimiter,
    ) {
    }

    public function limit(?string $key): bool
    {
        $limiter = $this->rateLimiter->create($key);
        $limit = $limiter->consume();

        return !$limit->isAccepted();
    }
}
