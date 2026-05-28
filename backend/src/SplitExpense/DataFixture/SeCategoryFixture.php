<?php

namespace App\SplitExpense\DataFixture;

use App\SplitExpense\Entity\SeCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SeCategoryFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $category = new SeCategory(tag: 'other', title: 'Other');
        $manager->persist($category);
        $this->addReference('se-category', $category);

        foreach (range(1, 5) as $i) {
            $category = new SeCategory(tag: 'other'.$i, title: 'Other'.$i);
            $manager->persist($category);
        }
        $manager->flush();
    }
}
