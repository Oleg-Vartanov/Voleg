<?php

namespace App\SplitExpense\Entity;

use App\Core\Entity\Currency;
use App\Core\Enum\Group;
use App\SplitExpense\Repository\SeAdjustmentRepository;
use App\User\Entity\User;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
#[ORM\Entity(repositoryClass: SeAdjustmentRepository::class)]
class SeAdjustment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private readonly User $createdByUser,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private readonly User $otherUser,
        #[ORM\Column(type: Types::INTEGER)]
        private int $amount,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        private Currency $currency,
        #[ORM\Column(type: Types::DATE_IMMUTABLE)]
        private DateTimeImmutable $adjustmentDate,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $description = null,
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedByUser(): User
    {
        return $this->createdByUser;
    }

    public function getOtherUser(): User
    {
        return $this->otherUser;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): void
    {
        $this->currency = $currency;
    }

    public function getAdjustmentDate(): DateTimeImmutable
    {
        return $this->adjustmentDate;
    }

    public function setAdjustmentDate(DateTimeImmutable $adjustmentDate): void
    {
        $this->adjustmentDate = $adjustmentDate;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
