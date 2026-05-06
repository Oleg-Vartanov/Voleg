<?php

namespace App\User\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\User\Entity\User;
use App\User\Entity\UserPasswordReset;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<UserPasswordReset>
 *
 * @method UserPasswordReset|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPasswordReset|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method UserPasswordReset[] findAll()
 * @method UserPasswordReset[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class UserPasswordResetRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPasswordReset::class);
    }

    public function findBySelector(string $value): ?UserPasswordReset
    {
        return $this->findOneBy(['selector' => $value]);
    }

    public function removeByUser(User $user, bool $flush = false): void
    {
        $entities = $this->findBy(['user' => $user]);
        if ($entities !== []) {
            foreach ($entities as $entity) {
                $this->remove($entity, $flush);
            }
        }
    }
}
