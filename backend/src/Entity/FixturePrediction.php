<?php

namespace App\Entity;

use App\Repository\FixturePredictionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FixturePredictionRepository::class)]
class FixturePrediction
{
    const SHOW_PREDICTIONS = 'ShowPredictions';

    #[Groups([self::SHOW_PREDICTIONS])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Fixture::class, inversedBy: 'fixturePredictions')]
    #[ORM\JoinColumn(nullable: false)]
    private Fixture $fixture;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[Groups([self::SHOW_PREDICTIONS])]
    #[ORM\Column(nullable: false)]
    private int $homeScore;

    #[Groups([self::SHOW_PREDICTIONS])]
    #[ORM\Column(nullable: false)]
    private int $awayScore;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFixture(): Fixture
    {
        return $this->fixture;
    }

    public function setFixture(Fixture $fixture): static
    {
        $this->fixture = $fixture;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getHomeScore(): int
    {
        return $this->homeScore;
    }

    public function setHomeScore(int $homeScore): static
    {
        $this->homeScore = $homeScore;

        return $this;
    }

    public function getAwayScore(): int
    {
        return $this->awayScore;
    }

    public function setAwayScore(int $awayScore): static
    {
        $this->awayScore = $awayScore;

        return $this;
    }
}
