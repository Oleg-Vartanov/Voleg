<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Fixture;
use App\Entity\Season;
use App\Entity\User;
use DateTime;
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
        ?User $user = null,
        ?Competition $competition = null,
        ?Season $season = null,
        ?int $round = null,
        ?DateTime $start = null,
        ?DateTime $end = null,
    ) {
        $qb = $this->createQueryBuilder('f')
            ->addSelect('fp', 'ht', 'at')
            ->leftJoin('f.fixturePredictions', 'fp')
            ->leftJoin('f.homeTeam', 'ht')
            ->leftJoin('f.awayTeam', 'at')
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
        if ($round !== null) {
            $qb->andWhere('f.matchday = :matchday')
               ->setParameter('matchday', $round);
        }
        if ($start !== null) {
            $qb->andWhere('f.startAt >= :start')
               ->setParameter('start', $start);
        }
        if ($end !== null) {
            $qb->andWhere('f.startAt <= :end')
               ->setParameter('end', $end);
        }

        return $qb->orderBy('f.startAt', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
}
