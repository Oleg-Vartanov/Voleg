<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Season;
use App\Entity\User;
use DateTime;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, User::class);
    }

    /** Used to upgrade (rehash) the user's password automatically over time. */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @return User[]
     */
    public function fixturesLeaderboard(
        ?Competition $competition = null,
        ?Season $season = null,
        ?DateTimeInterface $start = null,
        ?DateTimeInterface $end = null,
        ?int $limit = null,
    ): array {
        $qb = $this->createQueryBuilder('u')
            ->addSelect('u as user','SUM(fp.points) AS totalPoints')
            ->addSelect('SUM(CASE WHEN f.startAt >= :start AND f.startAt <= :end THEN fp.points ELSE 0 END) AS periodPoints')
            ->leftJoin('u.fixturePredictions', 'fp')
            ->leftJoin('fp.fixture', 'f');

        if ($competition !== null) {
            $qb->andWhere('f.competition = :competition')
               ->setParameter('competition', $competition);
        }
        if ($season !== null) {
            $qb->andWhere('f.season = :season')
               ->setParameter('season', $season);
        }

        $qb->setParameter('start', $start ?? new DateTime('0001-01-01'));
        $qb->setParameter('end', $end ?? new DateTime('9999-12-31'));

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->groupBy('u.id')
                  ->orderBy('totalPoints', 'DESC')
                  ->getQuery()
                  ->getResult();
    }
}
