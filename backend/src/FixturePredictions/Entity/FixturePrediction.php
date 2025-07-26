<?php

namespace App\FixturePredictions\Entity;

use App\FixturePredictions\Repository\FixturePredictionRepository;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FixturePredictionRepository::class)]
class FixturePrediction
{
    public const string SHOW_PREDICTIONS = 'ShowPredictions';

    #[Groups([self::SHOW_PREDICTIONS])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Fixture::class, inversedBy: 'fixturePredictions')]
    #[ORM\JoinColumn(nullable: false)]
    private Fixture $fixture;

    #[Groups([self::SHOW_PREDICTIONS])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[Groups([self::SHOW_PREDICTIONS])]
    #[ORM\Column(nullable: false)]
    private int $homeScore;

    #[Groups([self::SHOW_PREDICTIONS])]
    #[ORM\Column(nullable: false)]
    private int $awayScore;

    #[Groups([self::SHOW_PREDICTIONS])]
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $points = null;

    public function getId(): ?int
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

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): static
    {
        $this->points = $points;

        return $this;
    }
}
