<?php

namespace App\SplitExpense\Entity;

use App\Core\Enum\Group;
use App\SplitExpense\Repository\SeCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::PUBLIC])]
#[ORM\Entity(repositoryClass: SeCategoryRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SE_CATEGORY_TAG', fields: ['tag'])]
class SeCategory
{
    public const int DEFAULT_ID = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(length: 64)]
        private readonly string $tag,
        #[ORM\Column(length: 255)]
        private readonly string $title,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
