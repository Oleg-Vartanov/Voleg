<?php

namespace App\FixturePredictions\Repository;

use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Season>
 *
 * @method Season|null find($id, $lockMode = null, $lockVersion = null)
 * @method Season|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method Season[] findAll()
 * @method Season[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class SeasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
    }

    public function findOneByYear(int $year): ?Season
    {
        return $this->findOneBy(['year' => $year]);
    }

    public function findCurrentByCompetition(Competition $competition): ?Season
    {
        $year = $competition->getCurrentSeason()?->getYear();

        return null === $year ? null : $this->findOneByYear($year);
    }

    public function findByYearOrCompetition(
        ?int $year,
        ?Competition $competition,
        bool $defaultToCurrentSeason = false,
    ): ?Season {
        $season = null;
        if ($year !== null) {
            $season = $this->findOneByYear($year);
        } elseif ($defaultToCurrentSeason && $competition !== null) {
            $season = $this->findCurrentByCompetition($competition);
        }

        return $season;
    }
}
