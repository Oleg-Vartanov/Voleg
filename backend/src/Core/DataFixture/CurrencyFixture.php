<?php

namespace App\Core\DataFixture;

use App\Core\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CurrencyFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $usd = new Currency('USD', 2, '$');
        $eur = new Currency('EUR', 2, '€');
        $manager->persist($usd);
        $manager->persist($eur);

        foreach (range(1, 50) as $i) {
            $manager->persist(new Currency('C'.$i, 2, 'S'.$i));
        }

        $manager->flush();
    }
}
