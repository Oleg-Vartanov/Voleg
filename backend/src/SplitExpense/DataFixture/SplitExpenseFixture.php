<?php

namespace App\SplitExpense\DataFixture;

use App\Core\Entity\Currency;
use App\Core\Repository\CurrencyRepository;
use App\SplitExpense\Entity\SeCategory;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\Entity\SeExpenseSplit;
use App\SplitExpense\Repository\SeCategoryRepository;
use App\SplitExpense\Service\Seeder\SeCategorySeeder;
use App\User\DataFixture\UserFixture;
use App\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

class SplitExpenseFixture extends Fixture implements DependentFixtureInterface
{
    /** Currency code from reference data used for demo expenses. */
    public const string CURRENCY_CODE = 'USD';

    public function __construct(
        private readonly SeCategoryRepository $categoryRepository,
        private readonly CurrencyRepository $currencyRepository,
    ) {
    }

    /**
     * @return class-string[]
     */
    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $category = $this->categoryRepository->findOneBy(['tag' => SeCategorySeeder::DEFAULT_TAG]);
        if ($category === null) {
            throw new RuntimeException(
                sprintf('Category "%s" must be seeded before loading split expenses.', SeCategorySeeder::DEFAULT_TAG),
            );
        }

        $currency = $this->currencyRepository->findOneBy(['code' => self::CURRENCY_CODE]);
        if ($currency === null) {
            throw new RuntimeException(
                sprintf('Currency "%s" must be seeded before loading split expenses.', self::CURRENCY_CODE),
            );
        }

        $user = $this->getReference(UserFixture::REF_USER, User::class);

        foreach (range(1, 9) as $i) {
            $userA = $this->getReference(UserFixture::refUser($i), User::class);
            $userB = $this->getReference(UserFixture::refUser($i + 1), User::class);
            $manager->persist($this->createExpense($userA, $userB, $category, $currency));

            $userA = random_int(0, 1) ? $user : $userA;
            $userB = $userA->getId() === $user->getId() ? $userB : $user;
            $manager->persist($this->createExpense($userA, $userB, $category, $currency));
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
            ),
        );
        $expense->addSplit(
            new SeExpenseSplit(
                expense: $expense,
                user: $userB,
                amount: 5000,
            ),
        );

        return $expense;
    }
}
