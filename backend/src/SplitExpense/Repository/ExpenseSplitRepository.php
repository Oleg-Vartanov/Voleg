<?php

namespace App\SplitExpense\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\SplitExpense\Entity\ExpenseSplit;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<ExpenseSplit>
 *
 * @method ExpenseSplit|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpenseSplit|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method ExpenseSplit[] findAll()
 * @method ExpenseSplit[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseSplitRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpenseSplit::class);
    }
}
