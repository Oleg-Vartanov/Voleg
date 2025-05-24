<?php

namespace App\FixturePredictions\Repository;

use App\FixturePredictions\Entity\FixturePrediction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FixturePrediction>
 */
class FixturePredictionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FixturePrediction::class);
    }
}
