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
    /**
     * @return class-string[]
     */
    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            CurrencyFixture::class,
            SeCategoryFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $category = $this->getReference('se-category', SeCategory::class);
        $currency = $this->getReference(CurrencyFixture::CURRENCY, Currency::class);
        $user = $this->getReference('user', User::class);

        foreach (range(1, 9) as $i) {
            $userA = $this->getReference('user' . $i, User::class);
            $userB = $this->getReference('user' . ($i + 1), User::class);
            $expense = $this->createExpense($userA, $userB, $category, $currency);
            $manager->persist($expense);

            // Add expense for user@test.com
            $userA = random_int(0, 1) ? $user : $userA;
            $userB = $userA->getId() === $user->getId() ? $userB : $user;
            $expense = $this->createExpense($userA, $userB, $category, $currency);
            $manager->persist($expense);
        }

        $manager->flush();
    }

    private function createExpense(
        User $userA,
        User $userB,
        SeCategory $category,
        Currency $currency,
    ): SeExpense {
        $expense = new SeExpense(
            paidByUser: $userA,
            createdByUser: $userA,
            category: $category,
            amount: 10000,
            title: 'Test expense',
            currency: $currency,
            expenseDate: new \DateTimeImmutable(),
            description: 'Test expense description',
        );

        $expense->addSplit(
            new SeExpenseSplit(
                expense: $expense,
                user: $userA,
                amount: 5000,
            )
        );
        $expense->addSplit(
            new SeExpenseSplit(
                expense: $expense,
                user: $userB,
                amount: 5000,
            )
        );

        return $expense;
    }
}
