<?php

namespace App\Core\DataFixture;

use App\Core\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CountryFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $c = new Country();
        $c->setName('England');
        $c->setIso31661Alpha2('GB');
        $c->setIso31661Alpha3('GBR');
        $c->setIso31661Numeric(826);
        $c->setIso31662('GB-ENG');

        $manager->persist($c);
        $manager->flush();

        $this->addReference('country-GB', $c);
    }
}
