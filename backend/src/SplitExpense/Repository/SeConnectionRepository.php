<?php

namespace App\SplitExpense\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\SplitExpense\Entity\SeConnection;
use App\SplitExpense\Enum\SeConnectionDirectionEnum;
use App\SplitExpense\Enum\SeConnectionStatusEnum;
use App\User\Entity\User;
use Doctrine\ORM\QueryBuilder;
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
        ?SeConnectionStatusEnum $status = null,
        ?string $username = null,
        ?SeConnectionDirectionEnum $direction = null,
    ): array {
        $qb = $this->createFilteredQueryBuilder($user, $status, $username, $direction)
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        /** @var SeConnection[] $rows */
        $rows = $qb->getQuery()->getResult();

        return $rows;
    }

    public function countForUser(
        User $user,
        ?SeConnectionStatusEnum $status = null,
        ?string $username = null,
        ?SeConnectionDirectionEnum $direction = null,
    ): int {
        $count = $this->createFilteredQueryBuilder($user, $status, $username, $direction)
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count;
    }

    private function createFilteredQueryBuilder(
        User $user,
        ?SeConnectionStatusEnum $status = null,
        ?string $username = null,
        ?SeConnectionDirectionEnum $direction = null,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('c')
            ->innerJoin('c.userA', 'userA')
            ->innerJoin('c.userB', 'userB')
            ->where('c.userA = :user OR c.userB = :user')
            ->setParameter('user', $user);

        if ($direction !== null) {
            $qb->andWhere('c.status = :pendingStatus');
            $qb->setParameter('pendingStatus', SeConnectionStatusEnum::PENDING);

            if ($direction === SeConnectionDirectionEnum::INCOMING) {
                $qb->andWhere('c.requestedBy != :user');
            } else {
                $qb->andWhere('c.requestedBy = :user');
            }
        } elseif ($status !== null) {
            $qb->andWhere('c.status = :status');
            $qb->setParameter('status', $status);
        }

        if ($username !== null && $username !== '') {
            $qb->andWhere(
                '(c.userA = :user AND LOWER(userB.username) LIKE LOWER(:username))
                 OR (c.userB = :user AND LOWER(userA.username) LIKE LOWER(:username))'
            )->setParameter('username', '%' . $username . '%');
        }

        return $qb;
    }
}
