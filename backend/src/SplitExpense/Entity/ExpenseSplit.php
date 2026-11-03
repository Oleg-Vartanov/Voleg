<?php

namespace App\SplitExpense\Entity;

use App\Core\Enum\Group;
use App\SplitExpense\Repository\ExpenseSplitRepository;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ExpenseSplitRepository::class)]
#[ORM\Table(name: 'se_expense_split')]
#[ORM\UniqueConstraint(name: 'UNIQ_SE_EXPENSE_SPLIT', fields: ['expense', 'user'])]
class ExpenseSplit
{
    #[Groups(Group::public->value)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Expense::class, inversedBy: 'splits')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Expense $expense,
        #[Groups(Group::public->value)]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private User $user,
        #[Groups(Group::public->value)]
        #[ORM\Column(type: Types::INTEGER)]
        private int $amount,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpense(): Expense
    {
        return $this->expense;
    }

    public function setExpense(Expense $expense): void
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

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }
}
