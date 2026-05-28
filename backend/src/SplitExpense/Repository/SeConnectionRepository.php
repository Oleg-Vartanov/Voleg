<?php

namespace App\SplitExpense\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\SplitExpense\Entity\SeConnection;
use App\User\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<SeConnection>
 *
 * @method SeConnection|null find($id, $lockMode = null, $lockVersion = null)
 * @method SeConnection|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method SeConnection[] findAll()
 * @method SeConnection[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class SeConnectionRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeConnection::class);
    }

    public function findOneByUsers(
        User|int $userA,
        User|int $userB
    ): ?SeConnection {
        $userIdA = $userA instanceof User ? $userA->getId() : $userA;
        $userIdB = $userB instanceof User ? $userB->getId() : $userB;

        if ($userIdA === $userIdB) {
            return null;
        }
        if ($userIdA > $userIdB) {
            [$userA, $userB] = [$userB, $userA];
        }

        return $this->findOneBy(['userA' => $userA, 'userB' => $userB]);
    }

    /**
     * @return SeConnection[]
     */
    public function listForUser(
        User $user,
        int $offset = 0,
        int $limit = 100,
    ): array {
        return $this->createQueryBuilder('c')
            ->where('c.userA = :user OR c.userB = :user')
            ->setParameter('user', $user)
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
