<?php

namespace App\Core\DataFixture;

use App\Core\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CurrencyFixture extends Fixture
{
    public const string CURRENCY = 'currency';

    public function load(ObjectManager $manager): void
    {
        foreach (range(1, 50) as $i) {
            $c = new Currency('C' . $i, 2, 'S' . $i);
            $manager->persist($c);

            if ($i === 1) {
                $this->addReference(self::CURRENCY, $c);
            }
        }

        $manager->flush();
    }
}
