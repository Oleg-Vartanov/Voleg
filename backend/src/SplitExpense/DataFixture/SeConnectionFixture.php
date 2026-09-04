<?php

namespace App\SplitExpense\DataFixture;

use App\SplitExpense\Entity\SeConnection;
use App\SplitExpense\Enum\SeConnectionStatusEnum;
use App\User\DataFixture\UserFixture;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

class SeConnectionFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @return class-string[]
     */
    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $user1 = $this->userRepository->findByUsername('user1')
            ?? throw new RuntimeException('User "user1" must be seeded before loading connections.');
        $users = $this->findUsers(100);

        foreach ($users as $i => $user) {
            $seConnection = match (true) {
                $i < 15 => new SeConnection($user1, $user, SeConnectionStatusEnum::ACCEPTED),
                $i < 30 => new SeConnection($user, $user1, SeConnectionStatusEnum::ACCEPTED),
                $i < 45 => new SeConnection($user1, $user),
                $i < 60 => new SeConnection($user, $user1),
                $i < 70 => new SeConnection($user, $user1, SeConnectionStatusEnum::REJECTED),
                default => new SeConnection($user1, $user, SeConnectionStatusEnum::REJECTED),
            };

            $manager->persist($seConnection);
        }

        $manager->flush();
    }

    /**
     * @return User[]
     */
    private function findUsers(int $limit): array
    {
        /** @var User[] $users */
        $users = $this->userRepository->createQueryBuilder('u')
            ->where('u.username NOT IN (:usernames)')
            ->setParameter('usernames', ['admin', 'user1'])
            ->orderBy('u.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $users;
    }
}
