<?php

namespace App\SplitExpense\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\SplitExpense\Entity\Adjustment;
use App\SplitExpense\ValueObject\BalanceEntry;
use App\User\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<Adjustment>
 *
 * @method Adjustment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Adjustment|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method Adjustment[] findAll()
 * @method Adjustment[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class AdjustmentRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adjustment::class);
    }

    /**
     * @return Adjustment[]
     */
    public function listForUser(
        User $user,
        int $offset = 0,
        int $limit = 100,
    ): array {
        $qb = $this->createWithRelationsQueryBuilder()
            ->where('a.createdByUser = :user OR a.otherUser = :user')
            ->setParameter('user', $user)
            ->orderBy('a.adjustmentDate', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        /** @var Adjustment[] $rows */
        $rows = $qb->getQuery()->getResult();

        return $rows;
    }

    /**
     * @param int[] $ids
     *
     * @return Adjustment[]
     */
    public function findByIdsWithRelations(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $qb = $this->createWithRelationsQueryBuilder()
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $ids);

        /** @var Adjustment[] $rows */
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
     * @return BalanceEntry[]
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
     * @return BalanceEntry[]
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

    private function createWithRelationsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->addSelect('cu', 'ou', 'c')
            ->join('a.createdByUser', 'cu')
            ->join('a.otherUser', 'ou')
            ->join('a.currency', 'c');
    }

    /**
     * @return BalanceEntry[]
     */
    private function toBalanceEntries(QueryBuilder $qb): array
    {
        /** @var list<array{userId: int|string, currencyId: int|string, amount: int|string}> $result */
        $result = $qb->getQuery()->getScalarResult();

        $entries = [];
        foreach ($result as $row) {
            $entries[] = new BalanceEntry(
                userId: (int) $row['userId'],
                currencyId: (int) $row['currencyId'],
                amount: (int) $row['amount'],
            );
        }

        return $entries;
    }
}
