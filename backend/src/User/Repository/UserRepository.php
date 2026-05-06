<?php

namespace App\User\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\User\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends AbstractEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method User[] findAll()
 * @method User[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends AbstractEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findById(int $id): ?User
    {
        return $this->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /** Used to upgrade (rehash) the user's password automatically over time. */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->save($user, true);
    }

    /**
     * @return User[]
     */
    public function list(?string $tag, int $offset = 0, int $limit = 100): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($tag !== null) {
            $qb->where('u.tag = :tag')->setParameter('tag', $tag);
        }

        /** @var User[] $users */
        $users = $qb->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $users;
    }
}
