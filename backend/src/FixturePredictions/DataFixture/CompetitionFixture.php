<?php

namespace App\FixturePredictions\DataFixture;

use App\Core\DataFixture\CountryFixture;
use App\Core\Entity\Country;
use App\FixturePredictions\Entity\Competition;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CompetitionFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $country = $this->getReference('country-GB', Country::class);

        $c = new Competition();
        $c->setCode('PL');
        $c->setName('Premier League');
        $c->setCountry($country);

        $manager->persist($c);
        $manager->flush();

        $this->addReference('competition_PL', $c);
    }

    public function getDependencies(): array
    {
        return [
            CountryFixture::class,
        ];
    }
}
