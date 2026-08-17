<?php

namespace App\SplitExpense\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\ValueObject\SeBalanceEntry;
use App\User\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<SeExpense>
 *
 * @method SeExpense|null find($id, $lockMode = null, $lockVersion = null)
 * @method SeExpense|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method SeExpense[] findAll()
 * @method SeExpense[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class SeExpenseRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeExpense::class);
    }

    public function findOneForUser(int $userId, int $expenseId): ?SeExpense
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.splits', 's')
            ->where('e.id = :expenseId')
            ->andWhere('e.paidByUser = :userId OR s.user = :userId')
            ->setParameter('expenseId', $expenseId)
            ->setParameter('userId', $userId);

        /** @var SeExpense|null $result */
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result;
    }

    /**
     * @return SeExpense[]
     */
    public function listForUser(
        User $user,
        int $offset = 0,
        int $limit = 100
    ): array {
        $qb = $this->createQueryBuilder('e')
            ->distinct()
            ->leftJoin('e.splits', 's')
            ->leftJoin('e.currency', 'c')
            ->where('e.paidByUser = :user OR s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.expenseDate', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        /** @var SeExpense[] $rows */
        $rows = $qb->getQuery()->getResult();

        return $rows;
    }

    /**
     * @return SeBalanceEntry[]
     */
    public function sumSplitsOwedToUser(User $user): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select(
                'IDENTITY(s.user) AS userId',
                'IDENTITY(e.currency) AS currencyId',
                'SUM(s.amount) AS amount',
            )
            ->join('e.splits', 's')
            ->where('e.paidByUser = :user')
            ->andWhere('s.user != :user')
            ->groupBy('s.user')
            ->addGroupBy('e.currency')
            ->setParameter('user', $user);

        return $this->toBalanceEntries($qb);
    }

    /**
     * @return SeBalanceEntry[]
     */
    public function sumSplitsOwedByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select(
                'IDENTITY(e.paidByUser) AS userId',
                'IDENTITY(e.currency) AS currencyId',
                'SUM(s.amount) AS amount',
            )
            ->join('e.splits', 's')
            ->where('s.user = :user')
            ->andWhere('e.paidByUser != :user')
            ->groupBy('e.paidByUser')
            ->addGroupBy('e.currency')
            ->setParameter('user', $user);

        return $this->toBalanceEntries($qb);
    }

    public function countForUser(User $user): int
    {
        $count = $this->createQueryBuilder('e')
            ->select('COUNT(DISTINCT e.id)')
            ->leftJoin('e.splits', 's')
            ->where('e.paidByUser = :user OR s.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count;
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
