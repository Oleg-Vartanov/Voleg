<?php

namespace App\User\Service;

use App\Core\Util\PropertyAccessor;
use App\User\Entity\User;
use App\User\Http\V1\Request\UserDto;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserService
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

    public function patch(User $user, UserDto $dto): User
    {
        $props = array_flip(PropertyAccessor::getInitializedProperties($dto));

        if (isset($props['email'])) {
            $user->setEmail($dto->email);
        }
        if (isset($props['displayName'])) {
            $user->setDisplayName($dto->displayName);
        }
        if (isset($props['tag'])) {
            $user->setTag($dto->tag);
        }

        return $user;
    }
}
