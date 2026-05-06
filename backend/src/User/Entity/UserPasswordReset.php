<?php

namespace App\User\Entity;

use App\User\Repository\UserPasswordResetRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'User Password Reset')]
#[ORM\Entity(repositoryClass: UserPasswordResetRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_PASSWORD_RESET_SELECTOR', fields: ['selector'])]
class UserPasswordReset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 16)]
    private readonly string $selector;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private readonly User $user,
        #[ORM\Column(length: 64)]
        private readonly string $tokenHash,
        #[ORM\Column]
        private readonly DateTimeImmutable $expiresAt,
    ) {
        $this->selector = bin2hex(random_bytes(8));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable();
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }
}