<?php

namespace App\Core\Repository;

use App\Core\Entity\Currency;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<Currency>
 *
 * @method Currency|null find($id, $lockMode = null, $lockVersion = null)
 * @method Currency|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method Currency[] findAll()
 * @method Currency[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class CurrencyRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Currency::class);
    }

    /**
     * @return Currency[]
     */
    public function list(int $offset = 0, int $limit = 100): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.code')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
