<?php

namespace App\Repository;

use App\Entity\ApiToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiToken>
 */
class ApiTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiToken::class);
    }

    public function findOneByValue(string $value): ?ApiToken
    {
        return $this->findOneBy(['value' => $value]);
    }

    public function isUniqueValue(string $value): bool
    {
        return is_null($this->findOneByValue($value));
    }
}
