<?php

namespace App\User\EventListener;

use App\User\Model\Entity\User;
use App\User\Service\RefreshTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class RefreshTokenIssuer
{
    public function __construct(
        private RefreshTokenService $refreshTokens,
        private RequestStack $requestStack,
    ) {
    }

    #[AsEventListener(event: Events::AUTHENTICATION_SUCCESS)]
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $event->getUser();
        if (!$request instanceof Request || !$user instanceof User) {
            return;
        }

        $this->refreshTokens->start($user, $event->getResponse(), $request);
    }
}
