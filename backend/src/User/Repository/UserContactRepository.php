<?php

namespace App\User\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\User\Entity\User;
use App\User\Entity\UserContact;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<UserContact>
 *
 * @method UserContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserContact|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method UserContact[] findAll()
 * @method UserContact[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class UserContactRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserContact::class);
    }

    public function findOneByUsers(User|int $user, User|int $contact): ?UserContact
    {
        return $this->findOneBy(['user' => $user, 'contact' => $contact]);
    }

    /**
     * @return User[]
     */
    public function listContacts(User $user, int $offset = 0, int $limit = 100): array
    {
        /** @var User[] $contacts */
        $contacts = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('contact')
            ->from(User::class, 'contact')
            ->innerJoin(UserContact::class, 'uc', 'WITH', 'uc.contact = contact')
            ->where('uc.user = :user')
            ->setParameter('user', $user)
            ->orderBy('contact.tag')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $contacts;
    }
}
