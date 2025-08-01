<?php

namespace App\FixturePredictions\Entity;

use App\FixturePredictions\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[Groups([FixturePrediction::SHOW_PREDICTIONS])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([FixturePrediction::SHOW_PREDICTIONS])]
    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(nullable: true)]
    private ?int $providerTeamId = null;

    public function getId(): ?int
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
