<?php

namespace App\User\Entity;

use App\User\Repository\VerificationTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Confirms a new account's email. One per user.
 */
#[ORM\Entity(repositoryClass: VerificationTokenRepository::class)]
#[ORM\Table(name: 'user_verification_token')]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_VERIFICATION_TOKEN_HASH', fields: ['tokenHash'])]
class VerificationToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\OneToOne]
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

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable();
    }
}
