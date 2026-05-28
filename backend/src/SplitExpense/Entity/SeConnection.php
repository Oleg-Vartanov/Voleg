<?php

namespace App\SplitExpense\Entity;

use App\Core\Enum\Group;
use App\SplitExpense\Enum\SeConnectionStatusEnum;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\User\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
#[ORM\Entity(repositoryClass: SeConnectionRepository::class)]
#[ORM\UniqueConstraint(
    name: 'UNIQ_SE_CONNECTION_PAIR',
    fields: ['userA', 'userB'],
)]

#[ORM\Table(name: 'se_connection', options: [
    'check' => 'user_a_id < user_b_id',
])]
//ALTER TABLE se_connection
    //ADD CONSTRAINT chk_user_order
    //CHECK (user_a_id < user_b_id);
class SeConnection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private readonly User $requestedBy;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private User $userA,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private User $userB,
        #[ORM\Column(length: 16, enumType: SeConnectionStatusEnum::class)]
        private SeConnectionStatusEnum $status = SeConnectionStatusEnum::PENDING,
    ) {
        if ($userA === $userB) {
            throw new InvalidArgumentException('User A and B cannot be the same');
        }
        $this->requestedBy = $userA;
        if ($userA->getId() > $userB->getId()) {
            $this->userA = $userB;
            $this->userB = $userA;
        }
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserA(): User
    {
        return $this->userA;
    }

    public function getUserB(): User
    {
        return $this->userB;
    }

    public function getRequestedBy(): User
    {
        return $this->requestedBy;
    }

    public function getRequestedTo(): User
    {
        return $this->requestedBy === $this->userA
            ? $this->userB
            : $this->userA;
    }

    public function getStatus(): SeConnectionStatusEnum
    {
        return $this->status;
    }

    public function setStatus(SeConnectionStatusEnum $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function hasUser(User $user): bool
    {
        return $this->userA === $user || $this->userB === $user;
    }
}
