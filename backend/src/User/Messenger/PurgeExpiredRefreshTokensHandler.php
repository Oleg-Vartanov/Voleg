<?php

namespace App\User\Messenger;

use App\User\Repository\RefreshTokenRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class PurgeExpiredRefreshTokensHandler
{
    public function __construct(
        private RefreshTokenRepository $repository,
    ) {
    }

    public function __invoke(PurgeExpiredRefreshTokensMessage $message): void
    {
        $this->repository->removeExpired();
    }
}
