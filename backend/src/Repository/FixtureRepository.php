<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Fixture;
use App\Entity\Season;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fixture>
 */
class FixtureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fixture::class);
    }

    public function findOneByProviderFixtureId(int $id): ?Fixture
    {
        return $this->findOneBy(['providerFixtureId' => $id]);
    }

    public function filter(
        ?User $user,
        ?Competition $competition,
        ?Season $season,
    ) {
        $qb = $this->createQueryBuilder('f')
            ->addSelect('fp')
            ->leftJoin('f.fixturePredictions', 'fp')
        ;

        if ($user !== null) {
            $qb->andWhere('fp.user IS NULL OR fp.user = :user')
               ->setParameter('user', $user);
        }
        if ($competition !== null) {
            $qb->andWhere('f.competition = :competition')
               ->setParameter('competition', $competition);
        }
        if ($season !== null) {
            $qb->andWhere('f.season = :season')
               ->setParameter('season', $season);
        }

        return $qb->orderBy('f.matchday', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
}
