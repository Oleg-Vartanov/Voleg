<?php

namespace App\Factory;

use App\DTO\Auth\UserDto;
use App\Entity\User;
use InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserFactory
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function create(mixed $userData): User
    {
        return match (true) {
            is_array($userData) => $this->createByArray($userData),
            $userData instanceof UserDto => $this->createByUserDto($userData),
            default => throw new InvalidArgumentException('Can\'t create user by '.gettype($userData)),
        };
    }

    public function createByParams(
        string $email,
        string $password, // Plaintext password.
        array $roles = [],
        string $displayName = ''
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles($roles);
        $user->setDisplayName($displayName);

        return $user;
    }

    private function createByArray(array $params): User
    {
        return call_user_func_array([$this, 'createByParams'], $params);
    }

    private function createByUserDto(UserDto $userDto): User
    {
        return $this->createByArray($userDto->toArray());
    }
}