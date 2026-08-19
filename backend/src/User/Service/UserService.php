<?php

namespace App\User\Service;

use App\Core\Util\PropertyAccessor;
use App\User\Entity\User;
use App\User\Http\V1\Request\UserDto;
use Random\RandomException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EmailChangeService $emailChangeService,
    ) {
    }

    /**
     * @param array<string> $roles
     */
    public function create(
        string $email,
        string $plaintextPassword,
        string $username,
        array $roles = [],
    ): User {
        $user = new User();

        $user->setEmail($email);
        $this->setHashedPassword($user, $plaintextPassword);
        $user->setUsername($username);
        $user->setRoles($roles);

        return $user;
    }

    public function createByDto(UserDto $dto): User
    {
        return $this->create(
            email: $dto->email,
            plaintextPassword: $dto->password,
            username: $dto->username,
        );
    }

    /**
     * @throws TransportExceptionInterface|RandomException
     */
    public function patch(User $user, UserDto $dto): User
    {
        $props = array_flip(PropertyAccessor::getInitializedProperties($dto));

        if (isset($props['email']) && $dto->email !== $user->getEmail()) {
            $this->emailChangeService->requestEmailChange($user, $dto->email);
        }
        if (isset($props['username'])) {
            $user->setUsername($dto->username);
        }

        return $user;
    }

    public function hashPassword(User $user, string $plaintextPassword): string
    {
        return $this->passwordHasher->hashPassword($user, $plaintextPassword);
    }

    public function setHashedPassword(User $user, string $plaintextPassword): void
    {
        $user->setPassword($this->hashPassword($user, $plaintextPassword));
    }

    public function isPasswordValid(User $user, string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $plainPassword);
    }
}
