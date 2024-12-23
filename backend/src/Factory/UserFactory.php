<?php

namespace App\Factory;

use App\DTO\User\Request\UserDto;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserFactory
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function create(UserDto $dto): User
    {
        return $this->createByParams(
            email: $dto->email,
            password: $dto->password,
            displayName: $dto->displayName,
        );
    }

    public function createByParams(
        string $email,
        string $password, // Plaintext password.
        string $displayName,
        array $roles = [],
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setDisplayName($displayName);
        $user->setRoles($roles);

        return $user;
    }
}