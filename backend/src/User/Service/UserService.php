<?php

namespace App\User\Service;

use App\Core\Util\PropertyAccessor;
use App\User\Model\Entity\User;
use App\User\Http\V1\User\UserRequestPayload;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;
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
        #[SensitiveParameter] string $plaintextPassword,
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

    public function createByPayload(UserRequestPayload $payload): User
    {
        return $this->create(
            email: $payload->email,
            plaintextPassword: $payload->password,
            username: $payload->username,
        );
    }

    /**
     * @throws TransportExceptionInterface|RandomException|DateMalformedStringException
     */
    public function patch(User $user, UserRequestPayload $payload): User
    {
        $props = array_flip(PropertyAccessor::getInitializedProperties($payload));

        if (isset($props['email']) && $payload->email !== $user->getEmail()) {
            $this->emailChangeService->requestEmailChange($user, $payload->email);
        }
        if (isset($props['username'])) {
            $user->setUsername($payload->username);
        }

        return $user;
    }

    public function hashPassword(User $user, #[SensitiveParameter] string $plaintextPassword): string
    {
        return $this->passwordHasher->hashPassword($user, $plaintextPassword);
    }

    public function setHashedPassword(User $user, #[SensitiveParameter] string $plaintextPassword): void
    {
        $user->setPassword($this->hashPassword($user, $plaintextPassword));
    }

    public function isPasswordValid(User $user, #[SensitiveParameter] string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $plainPassword);
    }
}
