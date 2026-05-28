<?php

namespace App\SplitExpense\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\SplitExpense\Entity\SeExpenseSplit;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<SeExpenseSplit>
 *
 * @method SeExpenseSplit|null find($id, $lockMode = null, $lockVersion = null)
 * @method SeExpenseSplit|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method SeExpenseSplit[] findAll()
 * @method SeExpenseSplit[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class SeExpenseSplitRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeExpenseSplit::class);
    }
}
