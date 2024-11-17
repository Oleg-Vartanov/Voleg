<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(nullable: true)]
    private ?int $providerTeamId = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getProviderTeamId(): ?int
    {
        return $this->providerTeamId;
    }

    public function setProviderTeamId(?int $providerTeamId): static
    {
        $this->providerTeamId = $providerTeamId;

        return $this;
    }
}
