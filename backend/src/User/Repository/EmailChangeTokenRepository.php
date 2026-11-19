<?php

namespace App\User\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\User\Model\Entity\EmailChangeToken;
use App\User\Model\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<EmailChangeToken>
 *
 * @method EmailChangeToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailChangeToken|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method EmailChangeToken[] findAll()
 * @method EmailChangeToken[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class EmailChangeTokenRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailChangeToken::class);
    }

    public function findByTokenHash(string $hash): ?EmailChangeToken
    {
        return $this->findOneBy(['tokenHash' => $hash]);
    }

    public function findByUser(User $user): ?EmailChangeToken
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
