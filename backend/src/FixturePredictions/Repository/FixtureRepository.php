<?php

namespace App\FixturePredictions\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Fixture;
use App\FixturePredictions\Entity\Season;
use App\User\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<Fixture>
 *
 * @method Fixture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fixture|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method Fixture[] findAll()
 * @method Fixture[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class FixtureRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fixture::class);
    }

    public function findOneById(int $id): ?Fixture
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findOneByProviderFixtureId(int $id): ?Fixture
    {
        return $this->findOneBy(['providerFixtureId' => $id]);
    }

    /**
     * @param int[] $ids
     *
     * @return Fixture[]
     */
    public function findByProviderFixtureIds(array $ids): array
    {
        return $this->findBy(['providerFixtureId' => $ids]);
    }

    /**
     * @param User[] $users
     */
    public function filter(
        array $users,
        ?Competition $competition = null,
        ?Season $season = null,
        ?int $round = null,
        ?DateTimeImmutable $start = null,
        ?DateTimeImmutable $end = null,
        ?int $limit = null,
    ): mixed {
        $qb = $this->createQueryBuilder('f')
            ->addSelect('fp', 'ht', 'at')
            ->leftJoin('f.fixturePredictions', 'fp', Join::WITH, 'fp.user IN (:users) ')
            ->setParameter('users', $users)
            ->leftJoin('f.homeTeam', 'ht')
            ->leftJoin('f.awayTeam', 'at')
        ;

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

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->orderBy('f.startAt', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
}
