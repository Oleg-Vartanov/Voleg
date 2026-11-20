<?php

namespace App\User\Security;

use App\User\Enum\RoleEnum;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

readonly class JwtUser implements JWTUserInterface
{
    /**
     * @param non-empty-string $userIdentifier
     * @param string[] $roles
     */
    public function __construct(
        private int $id,
        private string $username,
        private array $roles,
        private string $userIdentifier,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @param non-empty-string $username
     * @param array{id?: int, username?: string, roles?: array<string>} $payload
     *
     * @return JWTUserInterface
     */
    public static function createFromPayload(
        $username,
        array $payload
    ): JWTUserInterface {
        // Named $username by the interface, but it holds the `user_id_claim` (lexik_jwt_authentication.yaml)
        $identifier = (string)$username;

        $id = $payload['id'] ?? $identifier;
        if (
            !is_numeric(
                $id
            ) || (int)$id < 1 || (string)(int)$id !== (string)$id
        ) {
            $exception = new UserNotFoundException('Invalid token identity.');
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        $roles = $payload['roles'] ?? [];
        $roles[] = RoleEnum::ROLE_USER->value;

        return new self(
            id: (int)$id,
            username: $payload['username'] ?? '',
            roles: array_values(array_unique($roles)),
            userIdentifier: $identifier,
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
    }
}
