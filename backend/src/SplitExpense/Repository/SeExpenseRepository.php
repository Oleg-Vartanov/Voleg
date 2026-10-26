<?php

namespace App\SplitExpense\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\Entity\SeExpenseSplit;
use App\SplitExpense\ValueObject\SeBalanceEntry;
use App\SplitExpense\ValueObject\SeCategoryTotalEntry;
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
        // Page ids first: with splits fetch-joined, LIMIT would count split rows instead of expenses.
        /** @var list<int|string> $ids */
        $ids = $this->createForUserQueryBuilder($user)
            ->select('e.id')
            ->orderBy('e.expenseDate', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getSingleColumnResult();

        return $this->findByIdsWithRelations(array_map(intval(...), $ids));
    }

    /**
     * Loads everything the API serializes, so reading the expenses triggers no lazy queries.
     *
     * @param int[] $ids
     *
     * @return SeExpense[]
     */
    public function findByIdsWithRelations(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $qb = $this->createQueryBuilder('e')
            ->addSelect('s', 'su', 'pu', 'cu', 'cat', 'c')
            ->leftJoin('e.splits', 's')
            ->leftJoin('s.user', 'su')
            ->join('e.paidByUser', 'pu')
            ->join('e.createdByUser', 'cu')
            ->join('e.category', 'cat')
            ->join('e.currency', 'c')
            ->where('e.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('e.expenseDate', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->addOrderBy('s.id', 'ASC');

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

    /**
     * The current user's own share of expenses, grouped by month, category and currency.
     *
     * @param string[] $months Months in YYYY-MM format.
     *
     * @return SeCategoryTotalEntry[]
     */
    public function sumUserSplitsByCategory(
        User $user,
        array $months,
        ?int $categoryId = null,
    ): array {
        if ($months === []) {
            return [];
        }

        $qb = $this->createQueryBuilder('e')
            ->select(
                "SUBSTRING(e.expenseDate, 1, 7) AS month",
                'IDENTITY(e.category) AS categoryId',
                'IDENTITY(e.currency) AS currencyId',
                'SUM(s.amount) AS amount',
            )
            ->join('e.splits', 's')
            ->where('s.user = :user')
            ->andWhere("SUBSTRING(e.expenseDate, 1, 7) IN (:months)")
            ->groupBy('month')
            ->addGroupBy('e.category')
            ->addGroupBy('e.currency')
            ->orderBy('month', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('months', $months);

        if ($categoryId !== null) {
            $qb->andWhere('e.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        /** @var list<array{
         *     month: string,
         *     categoryId: int|string,
         *     currencyId: int|string,
         *     amount: int|string
         * }> $result
         */
        $result = $qb->getQuery()->getScalarResult();

        return array_map(
            static fn (array $row): SeCategoryTotalEntry => new SeCategoryTotalEntry(
                month: $row['month'],
                categoryId: (int) $row['categoryId'],
                currencyId: (int) $row['currencyId'],
                amount: (int) $row['amount'],
            ),
            $result,
        );
    }

    /**
     * @return string[] Months in YYYY-MM format, newest first.
     */
    public function listUserSplitMonths(User $user, ?int $categoryId = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select("DISTINCT SUBSTRING(e.expenseDate, 1, 7) AS month")
            ->join('e.splits', 's')
            ->where('s.user = :user')
            ->orderBy('month', 'DESC')
            ->setParameter('user', $user);

        if ($categoryId !== null) {
            $qb->andWhere('e.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        /** @var string[] $months */
        $months = $qb->getQuery()->getSingleColumnResult();

        return $months;
    }

    public function countForUser(User $user): int
    {
        $count = $this->createForUserQueryBuilder($user)
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count;
    }

    /**
     * Expenses the user paid or has a split in. EXISTS keeps one row per expense and leaves
     * the splits association free to be fetch-joined in full.
     */
    private function createForUserQueryBuilder(User $user): QueryBuilder
    {
        $userSplit = $this->getEntityManager()->createQueryBuilder()
            ->select('us.id')
            ->from(SeExpenseSplit::class, 'us')
            ->where('us.expense = e')
            ->andWhere('us.user = :user');

        return $this->createQueryBuilder('e')
            ->where('e.paidByUser = :user OR EXISTS (' . $userSplit->getDQL() . ')')
            ->setParameter('user', $user);
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
