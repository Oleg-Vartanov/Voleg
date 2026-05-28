<?php

namespace App\SplitExpense\Entity;

use App\Core\Enum\Group;
use App\SplitExpense\Repository\SeExpenseSplitRepository;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SeExpenseSplitRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SE_EXPENSE_SPLIT', fields: ['expense', 'user'])]
class SeExpenseSplit
{
    #[Groups(Group::public->value)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: SeExpense::class, inversedBy: 'splits')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private SeExpense $expense,
        #[Groups(Group::public->value)]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private User $user,
        #[Groups(Group::public->value)]
        #[ORM\Column(type: Types::DECIMAL, precision: 19, scale: 4)]
        private string $amount,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpense(): SeExpense
    {
        return $this->expense;
    }

    public function setExpense(SeExpense $expense): void
    {
        $this->expense = $expense;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }
}
