<?php

namespace App\User\Model;

use App\User\Model\Entity\User;
use DateTimeImmutable;

/**
 * A refresh token that was just issued: the plain value to put in the cookie,
 * which is never stored, and when it expires.
 */
readonly class IssuedRefreshToken
{
    public function __construct(
        public User $user,
        public string $value,
        public DateTimeImmutable $expiresAt,
    ) {
    }
}
