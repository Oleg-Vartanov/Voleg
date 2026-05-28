<?php

namespace App\SplitExpense\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\SplitExpense\Entity\SeCategory;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<SeCategory>
 *
 * @method SeCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SeCategory|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method SeCategory[] findAll()
 * @method SeCategory[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 * /
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
