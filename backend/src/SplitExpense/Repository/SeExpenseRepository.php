<?php

namespace App\SplitExpense\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\SplitExpense\Entity\SeExpense;
use App\User\Entity\User;
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
        return $this->createQueryBuilder('e')
            ->leftJoin('e.splits', 's')
            ->where('e.id = :expenseId')
            ->andWhere('e.paidByUser = :userId OR s.user = :userId')
            ->setParameter('expenseId', $expenseId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return SeExpense[]
     */
    public function listForUser(
        User $user,
        int $offset = 0,
        int $limit = 100
    ): array {
        return $this->createQueryBuilder('e')
            ->distinct()
            ->leftJoin('e.splits', 's')
            ->where('e.paidByUser = :user OR s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.expenseDate', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
