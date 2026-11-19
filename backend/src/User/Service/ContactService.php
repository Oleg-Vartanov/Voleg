<?php

namespace App\User\Service;

use App\User\Entity\User;
use App\User\Entity\Contact;
use App\User\Repository\ContactRepository;
use LogicException;

readonly class ContactService
{
    public function __construct(
        private ContactRepository $repository,
    ) {
    }

    public function create(User $user, User $contact): Contact
    {
        if ($user->getId() === $contact->getId()) {
            throw new LogicException('Cannot add self as contact.');
        }

        if ($this->repository->findOneByUsers($user, $contact) !== null) {
            throw new LogicException('Contact already exists.');
        }

        return new Contact($user, $contact);
    }
}
