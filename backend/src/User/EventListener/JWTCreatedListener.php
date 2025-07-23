<?php

namespace App\User\EventListener;

use App\User\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Bundle\SecurityBundle\Security;

readonly class JWTCreatedListener
{
    public function __construct(private Security $security)
    {
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return;
        }

        $payload = $event->getData();
        $payload['id'] = $user->getId();
        $payload['displayName'] = $user->getDisplayName();

        $event->setData($payload);
    }
}
