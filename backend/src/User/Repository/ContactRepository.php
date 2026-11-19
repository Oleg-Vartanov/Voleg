<?php

namespace App\User\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\User\Model\Entity\User;
use App\User\Model\Entity\Contact;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<Contact>
 *
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method Contact[] findAll()
 * @method Contact[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    public function findOneByUsers(User|int $user, User|int $contact): ?Contact
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
            ->innerJoin(Contact::class, 'uc', 'WITH', 'uc.contact = contact')
            ->where('uc.user = :user')
            ->setParameter('user', $user)
            ->orderBy('contact.username')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $contacts;
    }
}
