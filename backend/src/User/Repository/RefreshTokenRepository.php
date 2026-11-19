<?php

namespace App\User\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\User\Model\Entity\RefreshToken;
use App\User\Model\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<RefreshToken>
 *
 * @method RefreshToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method RefreshToken|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method RefreshToken[] findAll()
 * @method RefreshToken[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class RefreshTokenRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findByTokenHash(string $hash): ?RefreshToken
    {
        return $this->findOneBy(['tokenHash' => $hash]);
    }

    public function removeAllForUser(User $user): void
    {
        $this->createQueryBuilder('refreshToken')
            ->delete()
            ->andWhere('refreshToken.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    /**
     * Expired rows are rejected anyway, so reuse detection no longer needs them.
     */
    public function removeExpired(): void
    {
        $this->createQueryBuilder('refreshToken')
            ->delete()
            ->andWhere('refreshToken.expiresAt < :now')
            ->setParameter('now', new DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
