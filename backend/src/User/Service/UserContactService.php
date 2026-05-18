<?php

namespace App\User\Service;

use App\User\Entity\User;
use App\User\Entity\UserContact;
use App\User\Repository\UserContactRepository;
use LogicException;

readonly class UserContactService
{
    public function __construct(
        private UserContactRepository $repository,
    ) {}

    public function create(User $user, User $contact): UserContact
    {
        if ($user->getId() === $contact->getId()) {
            throw new LogicException('Cannot add self as contact.');
        }

        if ($this->repository->findOneByUsers($user, $contact) !== null) {
            throw new LogicException('Contact already exists.');
        }

        return new UserContact($user, $contact);
    }
}