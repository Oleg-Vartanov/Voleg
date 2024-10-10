<?php

namespace App\Security;

use App\Repository\ApiTokenRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

readonly class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(private ApiTokenRepository $repository)
    {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $accessToken = $this->repository->findOneByValue($accessToken);
        if (is_null($accessToken) || !$accessToken->isValid()) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        return new UserBadge($accessToken->getUser()->getUserIdentifier());
    }
}