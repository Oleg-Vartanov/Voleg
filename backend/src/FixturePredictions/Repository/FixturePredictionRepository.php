<?php

namespace App\FixturePredictions\Repository;

use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Fixture;
use App\FixturePredictions\Entity\FixturePrediction;
use App\FixturePredictions\Entity\Season;
use App\User\Entity\User;
use DateTimeImmutable;
use DateTimeInterface;
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

    /**
     * @return FixturePrediction[]
     */
    public function findByFixture(Fixture $fixture): array
    {
        return $this->findBy(['fixture' => $fixture]);
    }

    /**
     * @return array<int, array{user: User, totalPoints: float, periodPoints: float}>
     */
    public function leaderboard(
        ?Competition $competition = null,
        ?Season $season = null,
        ?DateTimeInterface $start = null,
        ?DateTimeInterface $end = null,
        ?int $limit = null,
    ): array {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(
                'u AS user',
                'SUM(fp.points) AS totalPoints',
                'SUM(CASE WHEN f.startAt >= :start AND f.startAt <= :end THEN fp.points ELSE 0 END) AS periodPoints'
            )
            ->from(User::class, 'u')
            ->join('u.fixturePredictions', 'fp')
            ->join('fp.fixture', 'f');

        if ($competition !== null) {
            $qb->andWhere('f.competition = :competition')
               ->setParameter('competition', $competition);
        }

        if ($season !== null) {
            $qb->andWhere('f.season = :season')
               ->setParameter('season', $season);
        }

        $qb->setParameter('start', $start ?? new DateTimeImmutable('0001-01-01'));
        $qb->setParameter('end', $end ?? new DateTimeImmutable('9999-12-31'));

        $qb->groupBy('u.id')
           ->orderBy('totalPoints', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
