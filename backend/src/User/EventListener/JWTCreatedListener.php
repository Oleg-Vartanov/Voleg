<?php

namespace App\User\EventListener;

use App\User\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class JWTCreatedListener
{
    #[AsEventListener(event: Events::JWT_CREATED)]
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $payload = $event->getData();
        $payload['id'] = $user->getId();
        $payload['username'] = $user->getUsername();

        $event->setData($payload);
    }
}
