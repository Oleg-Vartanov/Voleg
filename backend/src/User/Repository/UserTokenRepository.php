<?php

namespace App\User\Repository;

use App\Core\Repository\AbstractEntityRepository;
use App\User\Entity\User;
use App\User\Entity\UserToken;
use App\User\Enum\UserTokenTypeEnum;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractEntityRepository<UserToken>
 *
 * @method UserToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserToken|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method UserToken[] findAll()
 * @method UserToken[] findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class UserTokenRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserToken::class);
    }

    public function findBySelector(string $value): ?UserToken
    {
        return $this->findOneBy(['selector' => $value]);
    }

    public function findOneByUser(
        User $user,
        ?UserTokenTypeEnum $type = null
    ): ?UserToken {
        $criteria = ['user' => $user];

        if ($type !== null) {
            $criteria['type'] = $type;
        }

        return $this->findOneBy($criteria);
    }

    public function removeByUser(
        User $user,
        UserTokenTypeEnum $type,
        bool $flush = false
    ): void {
        $entities = $this->findBy(['user' => $user, 'type' => $type]);
        if ($entities !== []) {
            foreach ($entities as $entity) {
                $this->remove($entity, $flush);
            }
        }
    }
}
