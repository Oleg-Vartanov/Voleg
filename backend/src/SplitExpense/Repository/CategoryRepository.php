<?php

namespace App\SplitExpense\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\SplitExpense\Entity\Category;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method Category[] findAll()
 * @method Category[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findOneByTag(string $tag): ?Category
    {
        return $this->findOneBy(['tag' => $tag]);
    }

    /**
     * @return Category[]
     */
    public function list(int $offset = 0, int $limit = 100): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.title')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        /** @var Category[] $rows */
        $rows = $qb->getQuery()->getResult();

        return $rows;
    }
}
