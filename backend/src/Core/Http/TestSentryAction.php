<?php

namespace App\Core\Http;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/test-sentry', name: 'testSentry', methods: [Request::METHOD_GET])]
readonly class TestSentryAction
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(): void
    {
        $this->logger->error('Test custom logger exception.');

        throw new \RuntimeException('Test RuntimeException.');
    }
}
