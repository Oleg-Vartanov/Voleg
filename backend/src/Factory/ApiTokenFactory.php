<?php

namespace App\Factory;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Repository\ApiTokenRepository;
use DateTimeImmutable;
use Random\RandomException;

readonly class ApiTokenFactory
{
    public function __construct(private ApiTokenRepository $repository)
    {
    }

    /** @throws RandomException */
    public function create(User $user): ApiToken
    {
        $token = new ApiToken();
        $token->setUser($user);
        $token->setExpiresAt((new DateTimeImmutable())->modify('+'.$token::EXPIRATION_TIME.' seconds'));
        $token->setValue($this->generateTokenValue());

        return $token;
    }

    /** @throws RandomException */
    private function generateTokenValue(): string
    {
        while (!isset($value) || !$this->repository->isUniqueValue($value)) {
            $value = bin2hex(random_bytes(30));
        }

        return $value;
    }
}