<?php

namespace App\SplitExpense\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\SplitExpense\Entity\SeAdjustment;
use App\SplitExpense\ValueObject\SeBalanceEntry;
use App\User\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<SeAdjustment>
 *
 * @method SeAdjustment|null find($id, $lockMode = null, $lockVersion = null)
 * @method SeAdjustment|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method SeAdjustment[] findAll()
 * @method SeAdjustment[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class SeAdjustmentRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeAdjustment::class);
    }

    /**
     * @return SeAdjustment[]
     */
    public function listForUser(
        User $user,
        int $offset = 0,
        int $limit = 100,
    ): array {
        $qb = $this->createQueryBuilder('a')
            ->where('a.createdByUser = :user OR a.otherUser = :user')
            ->setParameter('user', $user)
            ->orderBy('a.adjustmentDate', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        /** @var SeAdjustment[] $rows */
        $rows = $qb->getQuery()->getResult();

        return $rows;
    }

    public function countForUser(User $user): int
    {
        $count = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.createdByUser = :user OR a.otherUser = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count;
    }

    /**
     * @return SeBalanceEntry[]
     */
    public function sumCreatedByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select(
                'IDENTITY(a.otherUser) AS userId',
                'IDENTITY(a.currency) AS currencyId',
                'SUM(a.amount) AS amount',
            )
            ->where('a.createdByUser = :user')
            ->groupBy('a.otherUser')
            ->addGroupBy('a.currency')
            ->setParameter('user', $user);

        return $this->toBalanceEntries($qb);
    }

    /**
     * @return SeBalanceEntry[]
     */
    public function sumOtherUser(User $user): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select(
                'IDENTITY(a.createdByUser) AS userId',
                'IDENTITY(a.currency) AS currencyId',
                'SUM(a.amount) AS amount',
            )
            ->where('a.otherUser = :user')
            ->groupBy('a.createdByUser')
            ->addGroupBy('a.currency')
            ->setParameter('user', $user);

        return $this->toBalanceEntries($qb);
    }

    /**
     * @return SeBalanceEntry[]
     */
    private function toBalanceEntries(QueryBuilder $qb): array
    {
        /** @var list<array{userId: int|string, currencyId: int|string, amount: int|string}> $result */
        $result = $qb->getQuery()->getScalarResult();

        $entries = [];
        foreach ($result as $row) {
            $entries[] = new SeBalanceEntry(
                userId: (int) $row['userId'],
                currencyId: (int) $row['currencyId'],
                amount: (int) $row['amount'],
            );
        }

        return $entries;
    }
}
