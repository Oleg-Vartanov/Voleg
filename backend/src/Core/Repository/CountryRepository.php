<?php

namespace App\Core\Repository;

use App\Core\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Country>
 *
 * @method Country|null find($id, $lockMode = null, $lockVersion = null)
 * @method Country|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method Country[] findAll()
 * @method Country[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class CountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    public function findOneByName(string $name): ?Country
    {
        return $this->findOneBy(['name' => $name]);
    }
}
