<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Entity\Currency;
use App\Core\Repository\CurrencyRepository;
use App\Core\Test\ApiTestCase;
use App\SplitExpense\Entity\SeCategory;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\Entity\SeExpenseSplit;
use App\SplitExpense\Repository\SeCategoryRepository;
use App\SplitExpense\Repository\SeExpenseRepository;
use App\User\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeInsightsGetActionTest extends ApiTestCase
{
    private SeExpenseRepository $expenseRepository;
    private SeCategory $groceries;
    private SeCategory $restaurants;
    private Currency $currency;
    private Currency $otherCurrency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->expenseRepository = $this->getService(SeExpenseRepository::class);
        $categoryRepository = $this->getService(SeCategoryRepository::class);
        $this->groceries = $categoryRepository->findOneByTag('groceries');
        $this->restaurants = $categoryRepository->findOneByTag('restaurants');

        $currencies = $this->getService(CurrencyRepository::class)->list(0, 2);
        $this->currency = $currencies[0];
        $this->otherCurrency = $currencies[1];
    }

    #[TestDox('Insights GET: totals the current user share by month, category and currency')]
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

        self::assertCount(4, $data['categoryTotals']);
        self::assertSame(2, $data['totalMonthCount']);
        self::assertSame([
            [
                'month' => '2026-10',
                'category' => 'groceries',
                'currency' => $this->currency->getCode(),
                'amount' => 2500,
            ],
            [
                'month' => '2026-10',
                'category' => 'groceries',
                'currency' => $this->otherCurrency->getCode(),
                'amount' => 700,
            ],
            [
                'month' => '2026-10',
                'category' => 'restaurants',
                'currency' => $this->currency->getCode(),
                'amount' => 1200,
            ],
            [
                'month' => '2026-09',
                'category' => 'groceries',
                'currency' => $this->currency->getCode(),
                'amount' => 1500,
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
        self::assertSame(2, $firstPage['totalMonthCount']);

        $this->sendRequest(offset: 1, limit: 1);
        $secondPage = $this->getResponseData();
        self::assertCount(1, $secondPage['categoryTotals']);
        self::assertSame('2026-09', $secondPage['categoryTotals'][0]['month']);

        $this->sendRequest(categoryId: $this->restaurants->getId());
        $filtered = $this->getResponseData();
        self::assertCount(1, $filtered['categoryTotals']);
        self::assertSame('restaurants', $filtered['categoryTotals'][0]['category']['tag']);
        self::assertSame(1, $filtered['totalMonthCount']);
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
        SeCategory $category,
        Currency $currency,
        array $splits,
        DateTimeImmutable $expenseDate,
    ): void {
        $amount = array_sum(array_column($splits, 1));
        $expense = new SeExpense(
            paidByUser: $payer,
            createdByUser: $payer,
            category: $category,
            amount: $amount,
            title: 'Insights test expense',
            currency: $currency,
            expenseDate: $expenseDate,
        );

        foreach ($splits as [$user, $splitAmount]) {
            $expense->addSplit(new SeExpenseSplit($expense, $user, $splitAmount));
        }

        $this->expenseRepository->save($expense, true);
    }

    private function sendRequest(
        int $offset = 0,
        int $limit = 6,
        ?int $categoryId = null,
    ): void {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_insights_get', [
                'offset' => $offset,
                'limit' => $limit,
                'categoryId' => $categoryId,
            ]),
        );
    }
}
