<?php

namespace App\FixturePredictions\Entity;

use App\Core\Enum\Group;
use App\FixturePredictions\Repository\FixturePredictionRepository;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FixturePredictionRepository::class)]
#[ORM\Table(name: "fp_fixture_prediction")]
class FixturePrediction
{
    #[Groups([Group::public->value])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Fixture::class, inversedBy: 'fixturePredictions')]
    #[ORM\JoinColumn(nullable: false)]
    private Fixture $fixture;

    #[Groups([Group::public->value])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[Groups([Group::public->value])]
    #[ORM\Column(nullable: false)]
    private int $homeScore;

    #[Groups([Group::public->value])]
    #[ORM\Column(nullable: false)]
    private int $awayScore;

    #[Groups([Group::public->value])]
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

    public function setFixture(Fixture $fixture): void
    {
        $this->fixture = $fixture;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getHomeScore(): int
    {
        return $this->homeScore;
    }

    public function setHomeScore(int $homeScore): void
    {
        $this->homeScore = $homeScore;
    }

    public function getAwayScore(): int
    {
        return $this->awayScore;
    }

    public function setAwayScore(int $awayScore): void
    {
        $this->awayScore = $awayScore;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): void
    {
        $this->points = $points;
    }
}
