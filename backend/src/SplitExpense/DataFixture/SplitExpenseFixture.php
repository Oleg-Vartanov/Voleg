<?php

namespace App\SplitExpense\DataFixture;

use App\Core\DataFixture\CurrencyFixture;
use App\Core\Entity\Currency;
use App\SplitExpense\Entity\SeCategory;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\Entity\SeExpenseSplit;
use App\User\DataFixture\UserFixture;
use App\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SplitExpenseFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $category = $this->getReference('se-category', SeCategory::class);
        $currency = $this->getReference('currency-USD', Currency::class);

        foreach (range(1, 10) as $i) {
            $userA = $this->getReference('user'.$i, User::class);
            $userB = $this->getReference('user'.($i + 1), User::class);

            $expense = new SeExpense(
                paidByUser: $userA,
                category: $category,
                amount: '100.00',
                title: 'Test expense',
                currency: $currency,
                expenseDate: new \DateTimeImmutable(),
                description: 'Test expense description',
            );

            $expense->addSplit(
                new SeExpenseSplit(
                    expense: $expense,
                    user: $userA,
                    amount: '50.00',
                )
            );
            $expense->addSplit(
                new SeExpenseSplit(
                    expense: $expense,
                    user: $userB,
                    amount: '50.00',
                )
            );

            $manager->persist($expense);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            CurrencyFixture::class,
            SeCategoryFixture::class,
        ];
    }
}
