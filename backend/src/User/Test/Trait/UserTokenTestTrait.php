<?php

namespace App\User\Test\Trait;

use App\User\Repository\UserTokenRepository;
use App\User\Service\UserTokenService;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\Stub;

trait UserTokenTestTrait
{
    protected UserTokenRepository $userTokenRepository;
    protected Stub|UserTokenService $userTokenService;

    protected function tokenSetUp(): void
    {
        $this->userTokenRepository = static::getContainer()->get(UserTokenRepository::class);

        // Mock selector and secret generation.
        $this->userTokenService = $this->getStubBuilder(UserTokenService::class)
            ->onlyMethods(['generateSelector', 'generatePlainSecret', 'createExpiresAt'])
            ->getStub();
        static::getContainer()->set(UserTokenService::class, $this->userTokenService);
    }

    protected function mockToken(
        string $selector = 'test-selector',
        string $secret = 'test-secret',
        ?DateTimeImmutable $expiresAt = null,
    ): void {
        $this->userTokenService->method('generateSelector')->willReturn($selector);
        $this->userTokenService->method('generatePlainSecret')->willReturn($secret);
        $this->userTokenService->method('createExpiresAt')->willReturn(
            $expiresAt ?? new DateTimeImmutable(UserTokenService::TOKEN_EXPIRE_TIME)
        );
    }
}