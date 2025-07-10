<?php

namespace App\FixturePredictions\DataFixture;

use App\FixturePredictions\Entity\Fixture as FpFixture;
use App\FixturePredictions\Entity\FixturePrediction;
use App\User\DataFixture\UserFixture;
use App\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FixturePredictionFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $fixtures = [];
        foreach (range(1,380) as $index) {
            $fixtures[] = $this->getReference('fixture_'.$index, FpFixture::class);
        }

        $users = [
            $this->getReference('admin', User::class),
            $this->getReference('user', User::class),
        ];

        foreach ($users as $user) {
            foreach ($fixtures as $fixture) {
                $fp = new FixturePrediction();
                $fp->setFixture($fixture);
                $fp->setUser($user);
                $fp->setPoints([0, 1, 3][array_rand([0, 1, 3])]);
                $fp->setHomeScore(random_int(0,4));
                $fp->setAwayScore(random_int(0,4));

                $manager->persist($fp);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            FixtureFixture::class,
        ];
    }
}
