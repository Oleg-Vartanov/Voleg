<?php

namespace App\User\Messenger;

use App\User\Repository\EmailChangeTokenRepository;
use App\User\Repository\PasswordResetTokenRepository;
use App\User\Repository\RefreshTokenRepository;
use App\User\Repository\VerificationTokenRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class PurgeExpiredTokensHandler
{
    public function __construct(
        private RefreshTokenRepository $refreshTokens,
        private VerificationTokenRepository $verificationTokens,
        private EmailChangeTokenRepository $emailChangeTokens,
        private PasswordResetTokenRepository $passwordResetTokens,
    ) {
    }

    public function __invoke(PurgeExpiredTokensMessage $message): void
    {
        $this->refreshTokens->removeExpired();
        $this->verificationTokens->removeExpired();
        $this->emailChangeTokens->removeExpired();
        $this->passwordResetTokens->removeExpired();
    }
}
