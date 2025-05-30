<?php

namespace App\FixturePredictions\Repository;

use App\FixturePredictions\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Season>
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
}
