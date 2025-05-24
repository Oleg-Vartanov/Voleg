<?php

namespace App\FixturePredictions\Repository;

use App\FixturePredictions\Entity\Competition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competition>
 */
class CompetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competition::class);
    }

    public function findOneByCode(string $code): ?Competition
    {
        return $this->findOneBy(['code' => $code]);
    }
}
