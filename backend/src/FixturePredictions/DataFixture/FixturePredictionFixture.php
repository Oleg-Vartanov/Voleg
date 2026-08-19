<?php

namespace App\FixturePredictions\DataFixture;

use App\FixturePredictions\Entity\FixturePrediction;
use App\FixturePredictions\Repository\FixtureRepository;
use App\User\DataFixture\UserFixture;
use App\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

class FixturePredictionFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly FixtureRepository $fixtureRepository,
    ) {
    }

    /**
     * @return class-string[]
     */
    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            FixtureFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $fixtures = $this->fixtureRepository->findAll();
        if ($fixtures === []) {
            throw new RuntimeException('Fixtures must be loaded before predictions.');
        }

        $users = [
            $this->getReference(UserFixture::REF_ADMIN, User::class),
            $this->getReference(UserFixture::REF_USER, User::class),
        ];

        foreach ($users as $user) {
            foreach ($fixtures as $fixture) {
                $prediction = new FixturePrediction();
                $prediction->setFixture($fixture);
                $prediction->setUser($user);
                $prediction->setPoints([0, 1, 3][array_rand([0, 1, 3])]);
                $prediction->setHomeScore(random_int(0, 4));
                $prediction->setAwayScore(random_int(0, 4));

                $manager->persist($prediction);
            }
        }

        $manager->flush();
    }
}
