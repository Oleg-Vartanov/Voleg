<?php

namespace App\SplitExpense\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\SplitExpense\Entity\SeCategory;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<SeCategory>
 */
class SeCategoryRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeCategory::class);
    }

    /**
     * @return SeCategory[]
     */
    public function list(int $offset = 0, int $limit = 100): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.title')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
