<?php

namespace App\Factory;

use App\DTO\Auth\SignUpDto;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserFactory
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function create(array|SignUpDto $userData): User
    {
        return match (true) {
            is_array($userData) => $this->createByArray($userData),
            $userData instanceof SignUpDto => $this->createBySignUpDto($userData),
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

    private function createBySignUpDto(SignUpDto $dto): User
    {
        return $this->createByParams(
            email: $dto->email,
            password: $dto->password,
            displayName: $dto->displayName,
        );
    }
}