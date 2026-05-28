<?php

namespace App\SplitExpense\DataFixture;

use App\SplitExpense\Entity\SeCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SeCategoryFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (range(1, 5) as $i) {
            $manager->persist(
                new SeCategory(
                    tag: 'other'.$i,
                    title: 'Other'.$i,
                )
            );
        }
        $manager->flush();
    }
}
