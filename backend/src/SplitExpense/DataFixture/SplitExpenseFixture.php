<?php

namespace App\SplitExpense\DataFixture;

use App\Core\Repository\CurrencyRepository;
use App\SplitExpense\Entity\Expense;
use App\SplitExpense\Entity\ExpenseSplit;
use App\SplitExpense\Repository\CategoryRepository;
use App\SplitExpense\Service\Seeder\CategorySeeder;
use App\User\DataFixture\UserFixture;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

class SplitExpenseFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CategoryRepository $categoryRepository,
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
            ConnectionFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $category = $this->categoryRepository->findOneByTag(CategorySeeder::DEFAULT_TAG)
            ?? throw new RuntimeException('Categories must me seeded for fixtures');

        $currency = $this->currencyRepository->findOneByCode('USD')
            ?? throw new RuntimeException('Currencies must be seeded for fixtures');

        $firstDate = new DateTimeImmutable('2026-01-01');

        $user1 = $this->userRepository->findByUsername('user1')
            ?? throw new RuntimeException('User "user1" must be seeded before loading expenses.');
        $users = $this->findUsers(100);

        foreach ($users as $i => $user) {
            [$payer, $splitUser] = rand(0, 1)
                ? [$user1, $user]
                : [$user, $user1];

            $expense = new Expense(
                paidByUser: $payer,
                createdByUser: $payer,
                category: $category,
                amount: 6000,
                title: 'Test expense ' . $i,
                currency: $currency,
                expenseDate: $firstDate->modify("+{$i} weeks"),
                description: 'Test description ' . $i,
            );

            $expense->addSplit(new ExpenseSplit(expense: $expense, user: $payer, amount: 3000));
            $expense->addSplit(new ExpenseSplit(expense: $expense, user: $splitUser, amount: 3000));

            $manager->persist($expense);
        }

        $manager->flush();
    }

    /**
     * @return User[]
     */
    private function findUsers(int $limit): array
    {
        /** @var User[] $users */
        $users = $this->userRepository->createQueryBuilder('u')
            ->where('u.username NOT IN (:usernames)')
            ->setParameter('usernames', ['admin', 'user1'])
            ->orderBy('u.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $users;
    }
}
