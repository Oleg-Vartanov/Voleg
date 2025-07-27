<?php

namespace App\User\Factory;

use App\User\DTO\Request\UserDto;
use App\User\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserFactory
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * @param array<string> $roles
     */
    public function create(
        string $email,
        string $plaintextPassword,
        string $displayName,
        string $tag,
        array $roles = [],
    ): User {
        $user = new User();

        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plaintextPassword));
        $user->setDisplayName($displayName);
        $user->setTag($tag);
        $user->setRoles($roles);

        return $user;
    }

    public function createByDto(UserDto $dto): User
    {
        return $this->create(
            email: $dto->email,
            plaintextPassword: $dto->password,
            displayName: $dto->displayName,
            tag: $dto->tag,
        );
    }
}
