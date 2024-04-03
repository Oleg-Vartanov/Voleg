<?php

namespace App\Service;

use App\DTO\Auth\UserDto;
use App\Entity\User;
use App\Repository\UserRepository;

readonly class AuthService
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function createUser(UserDto $userDto): User
    {
        return $this->userRepository->create(
            email: $userDto->email,
            plaintextPassword: $userDto->password,
            displayName: $userDto->displayName
        );
    }
}