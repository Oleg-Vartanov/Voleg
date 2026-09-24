<?php

namespace App\FixturePredictions\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Fixture;
use App\FixturePredictions\Entity\FixturePrediction;
use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Http\V1\Leaderboard\LeaderboardRow;
use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<FixturePrediction>
 */
class FixturePredictionRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FixturePrediction::class);
    }

    /**
     * @return FixturePrediction[]
     */
    public function findByFixture(Fixture $fixture): array
    {
        return $this->findBy(['fixture' => $fixture]);
    }

    /**
     * @param int[]|null $userIds Limits the leaderboard to these users; null means all users.
     *
     * @return LeaderboardRow[]
     */
    public function leaderboard(
        ?Competition $competition = null,
        ?Season $season = null,
        ?array $userIds = null,
        ?DateTimeImmutable $start = null,
        ?DateTimeImmutable $end = null,
        ?int $limit = null,
        int $offset = 0,
    ): array {
        $qb = $this->createLeaderboardQueryBuilder($competition, $season, $userIds)
            ->select(
                'NEW ' . LeaderboardRow::class . '(
                    u,
                    SUM(fp.points),
                    COALESCE(
                        SUM(CASE
                            WHEN f.startAt >= :start AND f.startAt <= :end
                            THEN COALESCE(fp.points, 0)
                            ELSE 0
                        END),
                        0
                    )
                )'
            )
            ->setParameter('start', $start ?? new DateTimeImmutable('0001-01-01'))
            ->setParameter('end', $end ?? new DateTimeImmutable('9999-12-31'))
            ->groupBy('u.id')
            ->orderBy('SUM(fp.points)', 'DESC')
        ;

        if ($limit !== null) {
            $qb->setFirstResult($offset)
               ->setMaxResults($limit);
        }

        /** @var LeaderboardRow[] $rows */
        $rows = $qb->getQuery()->getResult();

        return $rows;
    }

    /**
     * @param int[]|null $userIds
     */
    public function countLeaderboard(
        ?Competition $competition = null,
        ?Season $season = null,
        ?array $userIds = null,
    ): int {
        $count = $this->createLeaderboardQueryBuilder($competition, $season, $userIds)
            ->select('COUNT(DISTINCT u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count;
    }

    /**
     * @param int[]|null $userIds
     */
    private function createLeaderboardQueryBuilder(
        ?Competition $competition = null,
        ?Season $season = null,
        ?array $userIds = null,
    ): QueryBuilder {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->from(FixturePrediction::class, 'fp')
            ->join('fp.user', 'u')
            ->join('fp.fixture', 'f');

        if ($competition !== null) {
            $qb->andWhere('f.competition = :competition')
               ->setParameter('competition', $competition);
        }

        if ($season !== null) {
            $qb->andWhere('f.season = :season')
               ->setParameter('season', $season);
        }

        if ($userIds !== null) {
            $qb->andWhere('u.id IN (:userIds)')
               ->setParameter('userIds', $userIds);
        }

        return $qb;
    }
}
