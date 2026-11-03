<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Entity\Currency;
use App\Core\Repository\CurrencyRepository;
use App\Core\Test\ApiTestCase;
use App\SplitExpense\Entity\Category;
use App\SplitExpense\Entity\Expense;
use App\SplitExpense\Entity\ExpenseSplit;
use App\SplitExpense\Repository\CategoryRepository;
use App\SplitExpense\Repository\ExpenseRepository;
use App\SplitExpense\Test\Trait\SplitExpenseTestTrait;
use App\User\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class InsightsGetActionTest extends ApiTestCase
{
    use SplitExpenseTestTrait;

    private ExpenseRepository $expenseRepository;
    private Category $groceries;
    private Category $restaurants;
    private Currency $currency;
    private Currency $otherCurrency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->expenseRepository = $this->getService(ExpenseRepository::class);
        $categoryRepository = $this->getService(CategoryRepository::class);
        $this->groceries = $categoryRepository->findOneByTag('groceries');
        $this->restaurants = $categoryRepository->findOneByTag('restaurants');

        $currencies = $this->getService(CurrencyRepository::class)->list(0, 2);
        $this->currency = $currencies[0];
        $this->otherCurrency = $currencies[1];
    }

    #[TestDox('Insights GET: totals expense cost paid by the current user by month, category and currency')]
    public function testCategoryTotals(): void
    {
        $user = $this->createUser(flush: false);
        $otherUser = $this->createUser();

        $this->createExpense(
            $user,
            $this->groceries,
            $this->currency,
            [[$user, 2500], [$otherUser, 7500]],
            new DateTimeImmutable('2026-10-20'),
        );
        $this->createExpense(
            $otherUser,
            $this->groceries,
            $this->currency,
            [[$user, 1500], [$otherUser, 3500]],
            new DateTimeImmutable('2026-09-30'),
        );
        $this->createExpense(
            $user,
            $this->restaurants,
            $this->currency,
            [[$user, 1200], [$otherUser, 800]],
            new DateTimeImmutable('2026-10-01'),
        );
        $this->createExpense(
            $user,
            $this->groceries,
            $this->otherCurrency,
            [[$user, 700], [$otherUser, 300]],
            new DateTimeImmutable('2026-10-05'),
        );

        $this->signIn($user);
        $this->sendRequest();

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->getResponseData();

        self::assertCount(3, $data['categoryTotals']);
        self::assertSame(1, $data['totalMonthCount']);
        self::assertSame([
            [
                'month' => '2026-10',
                'category' => 'groceries',
                'currency' => $this->currency->getCode(),
                'amount' => 10000,
            ],
            [
                'month' => '2026-10',
                'category' => 'groceries',
                'currency' => $this->otherCurrency->getCode(),
                'amount' => 1000,
            ],
            [
                'month' => '2026-10',
                'category' => 'restaurants',
                'currency' => $this->currency->getCode(),
                'amount' => 2000,
            ],
        ], array_map(
            static fn (array $total): array => [
                'month' => $total['month'],
                'category' => $total['category']['tag'],
                'currency' => $total['currency']['code'],
                'amount' => $total['amount'],
            ],
            $data['categoryTotals'],
        ));

        $this->sendRequest(limit: 1);
        $firstPage = $this->getResponseData();
        self::assertCount(3, $firstPage['categoryTotals']);
        self::assertSame(['2026-10'], array_values(array_unique(array_column(
            $firstPage['categoryTotals'],
            'month',
        ))));
        self::assertSame(1, $firstPage['totalMonthCount']);

        $this->sendRequest(offset: 1, limit: 1);
        $secondPage = $this->getResponseData();
        self::assertSame([], $secondPage['categoryTotals']);
        self::assertSame(1, $secondPage['totalMonthCount']);

        $this->sendRequest(categoryId: $this->restaurants->getId());
        $filtered = $this->getResponseData();
        self::assertCount(1, $filtered['categoryTotals']);
        self::assertSame('restaurants', $filtered['categoryTotals'][0]['category']['tag']);
        self::assertSame(2000, $filtered['categoryTotals'][0]['amount']);
        self::assertSame(1, $filtered['totalMonthCount']);
    }

    #[TestDox('Insights GET: includes total cost paid by selected users when the current user is on the split')]
    public function testIncludesSelectedUsers(): void
    {
        $user = $this->createUser(flush: false);
        $otherUser = $this->createUser(flush: false);
        $this->createConnection($user, $otherUser);

        $this->createExpense(
            $user,
            $this->groceries,
            $this->currency,
            [[$user, 2500], [$otherUser, 7500]],
            new DateTimeImmutable('2026-10-20'),
        );
        $this->createExpense(
            $otherUser,
            $this->restaurants,
            $this->currency,
            [[$user, 1000], [$otherUser, 3000]],
            new DateTimeImmutable('2026-09-15'),
        );
        // Paid by the other user, but the current user is not on the split — excluded.
        $this->createExpense(
            $otherUser,
            $this->groceries,
            $this->currency,
            [[$otherUser, 5000]],
            new DateTimeImmutable('2026-08-01'),
        );

        $this->signIn($user);
        $this->sendRequest(userIds: [$otherUser->getId()]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->getResponseData();

        self::assertSame(2, $data['totalMonthCount']);
        self::assertSame([
            [
                'month' => '2026-10',
                'category' => 'groceries',
                'currency' => $this->currency->getCode(),
                'amount' => 10000,
            ],
            [
                'month' => '2026-09',
                'category' => 'restaurants',
                'currency' => $this->currency->getCode(),
                'amount' => 4000,
            ],
        ], array_map(
            static fn (array $total): array => [
                'month' => $total['month'],
                'category' => $total['category']['tag'],
                'currency' => $total['currency']['code'],
                'amount' => $total['amount'],
            ],
            $data['categoryTotals'],
        ));
    }

    #[TestDox('Insights GET: rejects unconnected users')]
    public function testRejectsUnconnectedUsers(): void
    {
        $user = $this->createUser(flush: false);
        $stranger = $this->createUser();

        $this->signIn($user);
        $this->sendRequest(userIds: [$stranger->getId()]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Insights GET: returns an empty result when the user has no expenses')]
    public function testEmpty(): void
    {
        $this->signIn($this->createUser());
        $this->sendRequest();

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            ['categoryTotals' => [], 'totalMonthCount' => 0],
            $this->getResponseData(),
        );
    }

    #[TestDox('Insights GET: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param array<int, array{0: User, 1: int}> $splits
     */
    private function createExpense(
        User $payer,
        Category $category,
        Currency $currency,
        array $splits,
        DateTimeImmutable $expenseDate,
    ): void {
        $amount = array_sum(array_column($splits, 1));
        $expense = new Expense(
            paidByUser: $payer,
            createdByUser: $payer,
            category: $category,
            amount: $amount,
            title: 'Insights test expense',
            currency: $currency,
            expenseDate: $expenseDate,
        );

        foreach ($splits as [$user, $splitAmount]) {
            $expense->addSplit(new ExpenseSplit($expense, $user, $splitAmount));
        }

        $this->expenseRepository->save($expense, true);
    }

    /**
     * @param int[] $userIds
     */
    private function sendRequest(
        int $offset = 0,
        int $limit = 6,
        ?int $categoryId = null,
        array $userIds = [],
    ): void {
        $params = [
            'offset' => $offset,
            'limit' => $limit,
            'categoryId' => $categoryId,
        ];
        if ($userIds !== []) {
            $params['userIds'] = $userIds;
        }

        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_insights_get', $params),
        );
    }
}
