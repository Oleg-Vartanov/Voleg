<?php

namespace App\FixturePredictions\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Fixture;
use App\FixturePredictions\Entity\Season;
use App\User\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
     * Returns one page of fixtures, each with the predictions of the given users.
     *
     * @param User[] $users
     *
     * @return Fixture[]
     */
    public function filter(
        array $users,
        ?Competition $competition = null,
        ?Season $season = null,
        ?int $round = null,
        ?DateTimeImmutable $start = null,
        ?DateTimeImmutable $end = null,
        ?int $limit = null,
        int $offset = 0,
    ): array {
        $qb = $this->createFilteredQueryBuilder($competition, $season, $round, $start, $end)
            ->addSelect('fp', 'ht', 'at')
            ->leftJoin('f.fixturePredictions', 'fp', Join::WITH, 'fp.user IN (:users) ')
            ->setParameter('users', $users)
            ->leftJoin('f.homeTeam', 'ht')
            ->leftJoin('f.awayTeam', 'at')
            ->orderBy('f.startAt', 'ASC')
        ;

        if ($limit === null) {
            /** @var Fixture[] $fixtures */
            $fixtures = $qb->getQuery()->getResult();

            return $fixtures;
        }

        $query = $qb->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery();

        // Paginator applies limit to fixtures, not joined rows.
        return iterator_to_array(new Paginator($query));
    }

    public function countFiltered(
        ?Competition $competition = null,
        ?Season $season = null,
        ?int $round = null,
        ?DateTimeImmutable $start = null,
        ?DateTimeImmutable $end = null,
    ): int {
        $count = $this->createFilteredQueryBuilder($competition, $season, $round, $start, $end)
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count;
    }

    private function createFilteredQueryBuilder(
        ?Competition $competition = null,
        ?Season $season = null,
        ?int $round = null,
        ?DateTimeImmutable $start = null,
        ?DateTimeImmutable $end = null,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('f');

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

        return $qb;
    }
}
