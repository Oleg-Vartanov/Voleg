<?php

namespace App\User\Entity;

use App\User\Enum\UserTokenTypeEnum;
use App\User\Repository\UserTokenRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'User Token')]
#[ORM\Entity(repositoryClass: UserTokenRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_TOKEN_SELECTOR', fields: ['selector'])]
class UserToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\Column(enumType: UserTokenTypeEnum::class)]
        private readonly UserTokenTypeEnum $type,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private readonly User $user,
        #[ORM\Column(length: 32)]
        private readonly string $selector,
        #[ORM\Column(length: 64)]
        private readonly string $secret,
        #[ORM\Column]
        private readonly DateTimeImmutable $expiresAt,
        #[ORM\Column(type: Types::JSON)]
        private readonly array $payload = [],
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getType(): UserTokenTypeEnum
    {
        return $this->type;
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getEmailChange(): string
    {
        if ($this->type !== UserTokenTypeEnum::EMAIL_CHANGE) {
            throw new LogicException('Token is not an email change token.');
        }

        return $this->payload['emailChange'];
    }
}