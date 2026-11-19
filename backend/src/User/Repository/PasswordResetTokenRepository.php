<?php

namespace App\User\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\User\Model\Entity\PasswordResetToken;
use App\User\Model\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<PasswordResetToken>
 *
 * @method PasswordResetToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordResetToken|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method PasswordResetToken[] findAll()
 * @method PasswordResetToken[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class PasswordResetTokenRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    public function findByTokenHash(string $hash): ?PasswordResetToken
    {
        return $this->findOneBy(['tokenHash' => $hash]);
    }

    public function findByUser(User $user): ?PasswordResetToken
    {
        return $this->findOneBy(['user' => $user]);
    }

    /**
     * Deletes right away, so a new token for the same user can be inserted next.
     */
    public function removeForUser(User $user): void
    {
        $this->createQueryBuilder('token')
            ->delete()
            ->andWhere('token.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    public function removeExpired(): void
    {
        $this->createQueryBuilder('token')
            ->delete()
            ->andWhere('token.expiresAt < :now')
            ->setParameter('now', new DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
