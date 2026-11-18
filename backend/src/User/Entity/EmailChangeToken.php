<?php

namespace App\User\Entity;

use App\User\Repository\EmailChangeTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Confirms a requested email change, sent to the new address. One per user.
 */
#[ORM\Entity(repositoryClass: EmailChangeTokenRepository::class)]
#[ORM\Table(name: 'user_email_change_token')]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_EMAIL_CHANGE_TOKEN_HASH', fields: ['tokenHash'])]
class EmailChangeToken
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
        #[ORM\Column(length: 180)]
        private readonly string $newEmail,
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

    public function getNewEmail(): string
    {
        return $this->newEmail;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable();
    }
}
