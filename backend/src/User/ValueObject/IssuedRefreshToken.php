<?php

namespace App\User\ValueObject;

use App\User\Entity\User;
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
