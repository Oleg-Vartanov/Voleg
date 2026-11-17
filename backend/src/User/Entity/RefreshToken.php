<?php

namespace App\User\Entity;

use App\User\Repository\RefreshTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'user_refresh_token')]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_REFRESH_TOKEN_HASH', fields: ['tokenHash'])]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $usedAt = null;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private readonly User $user,
        #[ORM\Column(length: 64)]
        private readonly string $tokenHash,
        #[ORM\Column]
        private readonly DateTimeImmutable $expiresAt,
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

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable();
    }

    public function isUsed(): bool
    {
        return $this->usedAt !== null;
    }

    public function markUsed(): void
    {
        $this->usedAt = new DateTimeImmutable();
    }
}
