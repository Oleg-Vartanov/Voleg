<?php

namespace App\FixturePredictions\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\FixturePredictions\Entity\Competition;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<Competition>
 *
 * @method Competition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competition|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method Competition[] findAll()
 * @method Competition[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionRepository extends AbstractEntityRepository
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
