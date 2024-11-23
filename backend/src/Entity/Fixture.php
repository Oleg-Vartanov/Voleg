<?php

namespace App\Entity;

use App\Controller\FixturesController;
use App\Enum\Fixtures\FixtureStatusEnum;
use App\Repository\FixtureRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([FixturesController::SHOW_PREDICTIONS])]
#[ORM\Entity(repositoryClass: FixtureRepository::class)]
class Fixture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Competition $competition;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Season $season;

    #[ORM\Column(enumType: FixtureStatusEnum::class)]
    private FixtureStatusEnum $status = FixtureStatusEnum::Unknown;

    #[ORM\Column]
    private int $matchday;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Team $homeTeam;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Team $awayTeam;

    #[ORM\Column(nullable: true)]
    private ?int $homeScore = null;

    #[ORM\Column(nullable: true)]
    private ?int $awayScore = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $startAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $providerFixtureId = null;

    #[ORM\OneToMany(targetEntity: FixturePrediction::class, mappedBy: 'fixture')]
    /**
     * @var $fixturePredictions FixturePrediction[]|array|PersistentCollection
     */
    private PersistentCollection|array $fixturePredictions;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCompetition(): Competition
    {
        return $this->competition;
    }

    public function setCompetition(Competition $competition): static
    {
        $this->competition = $competition;

        return $this;
    }

    public function getSeason(): Season
    {
        return $this->season;
    }

    public function setSeason(Season $season): static
    {
        $this->season = $season;

        return $this;
    }

    public function getStatus(): FixtureStatusEnum
    {
        return $this->status;
    }

    public function setStatus(FixtureStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getMatchday(): int
    {
        return $this->matchday;
    }

    public function setMatchday(int $matchday): static
    {
        $this->matchday = $matchday;

        return $this;
    }

    public function getHomeTeam(): Team
    {
        return $this->homeTeam;
    }

    public function setHomeTeam(Team $homeTeam): static
    {
        $this->homeTeam = $homeTeam;

        return $this;
    }

    public function getAwayTeam(): Team
    {
        return $this->awayTeam;
    }

    public function setAwayTeam(Team $awayTeam): static
    {
        $this->awayTeam = $awayTeam;

        return $this;
    }

    public function getHomeScore(): ?int
    {
        return $this->homeScore;
    }

    public function setHomeScore(?int $homeScore): static
    {
        $this->homeScore = $homeScore;

        return $this;
    }

    public function getAwayScore(): ?int
    {
        return $this->awayScore;
    }

    public function setAwayScore(?int $awayScore): static
    {
        $this->awayScore = $awayScore;

        return $this;
    }

    public function getStartAt(): ?DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getProviderFixtureId(): ?int
    {
        return $this->providerFixtureId;
    }

    public function setProviderFixtureId(?int $providerFixtureId): static
    {
        $this->providerFixtureId = $providerFixtureId;

        return $this;
    }

    /** @return FixturePrediction[]|array|PersistentCollection */
    public function getFixturePredictions(): array|PersistentCollection
    {
        return $this->fixturePredictions;
    }
}
