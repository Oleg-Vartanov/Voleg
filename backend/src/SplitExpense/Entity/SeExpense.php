<?php

namespace App\SplitExpense\Entity;

use App\Core\Entity\Currency;
use App\Core\Enum\Group;
use App\SplitExpense\Repository\SeExpenseRepository;
use App\User\Entity\User;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
#[ORM\Entity(repositoryClass: SeExpenseRepository::class)]
class SeExpense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    /** @var Collection<int, SeExpenseSplit> */
    #[ORM\OneToMany(
        targetEntity: SeExpenseSplit::class,
        mappedBy: 'expense',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $splits;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private User $paidByUser,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        private SeCategory $category,
        #[ORM\Column(type: Types::DECIMAL, precision: 19, scale: 4)]
        private string $amount,
        #[ORM\Column(length: 255)]
        private string $title,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        private Currency $currency,
        #[ORM\Column(type: Types::DATE_IMMUTABLE)]
        private DateTimeImmutable $expenseDate,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $description = null,
    ) {
        $this->createdAt = new DateTimeImmutable();
        $this->splits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaidByUser(): User
    {
        return $this->paidByUser;
    }

    public function setPaidByUser(User $paidByUser): void
    {
        $this->paidByUser = $paidByUser;
    }

    public function getCategory(): SeCategory
    {
        return $this->category;
    }

    public function setCategory(SeCategory $category): void
    {
        $this->category = $category;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
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

    public function getExpenseDate(): DateTimeImmutable
    {
        return $this->expenseDate;
    }

    public function setExpenseDate(DateTimeImmutable $expenseDate): void
    {
        $this->expenseDate = $expenseDate;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, SeExpenseSplit>
     */
    public function getSplits(): Collection
    {
        return $this->splits;
    }

    public function addSplit(SeExpenseSplit $split): void
    {
        if (!$this->splits->contains($split)) {
            $this->splits->add($split);
            $split->setExpense($this);
        }
    }

    public function clearSplits(): void
    {
        $this->splits->clear();
    }
}
