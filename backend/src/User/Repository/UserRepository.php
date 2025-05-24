<?php

namespace App\User\Repository;

use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Season;
use App\User\Entity\User;
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
 * @method User[] findAll()
 * @method User[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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

    public function list(?string $tag, int $offset = 0, int $limit = 100): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($tag !== null) {
            $qb->where('u.tag = :tag')->setParameter('tag', $tag);
        }

        return $qb->setFirstResult($offset)
                  ->setMaxResults($limit)
                  ->getQuery()
                  ->getResult();
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

    public function tagExist(string $tag): bool
    {
        return (bool)$this->findOneBy(['tag' => $tag]);
    }

    public function findUserTagWithHighestNumber(string $baseTag): ?string
    {
        $result = $this->createQueryBuilder('u')
            ->addSelect('u.tag')
            ->where('u.tag LIKE :tag')
            ->andWhere('REGEXP(u.tag, :regexp) = true')
            ->setParameter('tag', $baseTag . '%')
            ->setParameter('regexp', $baseTag . '[0-9]+$')
            ->orderBy('u.tag', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result['tag'] ?? null;
    }
}
