<?php

namespace App\FixturePredictions\Repository;

use App\FixturePredictions\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function findOneByProviderTeamId(int $id): ?Team
    {
        return $this->findOneBy(['providerTeamId' => $id]);
    }
}
