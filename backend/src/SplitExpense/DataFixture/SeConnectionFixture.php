<?php

namespace App\SplitExpense\DataFixture;

use App\SplitExpense\Entity\SeConnection;
use App\SplitExpense\Enum\SeConnectionStatusEnum;
use App\User\DataFixture\UserFixture;
use App\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SeConnectionFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $userA = $this->getReference('user1', User::class);

        foreach (range(2, 5) as $i) {
            $userB = $this->getReference("user{$i}", User::class);
            $manager->persist(new SeConnection($userA, $userB));
        }

        foreach (range(6, 10) as $i) {
            $userB = $this->getReference("user{$i}", User::class);
            $manager->persist(
                new SeConnection(
                    $userA,
                    $userB,
                    SeConnectionStatusEnum::ACCEPTED
                )
            );
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}
