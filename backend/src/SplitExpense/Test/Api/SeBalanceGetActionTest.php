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
class SeBalanceGetActionTest extends ApiTestCase
{
    private SeExpenseRepository $expenseRepo;
    private SeCategory $category;
    private Currency $currency;
    private Currency $otherCurrency;

    public function setUp(): void
    {
        parent::setUp();

        $this->expenseRepo = $this->getService(SeExpenseRepository::class);
        $this->category = $this->getService(SeCategoryRepository::class)->find(SeCategory::DEFAULT_ID);

        $currencies = $this->getService(CurrencyRepository::class)->list(0, 2);
        $this->currency = $currencies[0];
        $this->otherCurrency = $currencies[1];
    }

    #[TestDox('Balance GET: nets both directions')]
    public function testSuccess(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser();

        $this->createExpense($userA, [[$userA, 4000], [$userB, 6000]]);
        $this->createExpense($userB, [[$userA, 2500], [$userB, 7500]]);

        $this->signIn($userA);
        $this->sendRequest();

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->getResponseData();

        self::assertCount(1, $data['totalAmounts']);
        self::assertSame($this->currency->getId(), $data['totalAmounts'][0]['currency']['id']);
        self::assertSame(3500, $data['totalAmounts'][0]['amount']);

        self::assertCount(1, $data['byUserAmounts']);
        self::assertSame($userB->getId(), $data['byUserAmounts'][0]['user']['id']);
        self::assertCount(1, $data['byUserAmounts'][0]['amounts']);
        self::assertSame(3500, $data['byUserAmounts'][0]['amounts'][0]['amount']);
    }

    #[TestDox('Balance GET: the counterparty sees the mirrored amount')]
    public function testCounterpartyOwes(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser();

        $this->createExpense($userA, [[$userA, 4000], [$userB, 6000]]);
        $this->createExpense($userB, [[$userA, 2500], [$userB, 7500]]);

        $this->signIn($userB);
        $this->sendRequest();

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->getResponseData();

        self::assertSame(-3500, $data['totalAmounts'][0]['amount']);
        self::assertSame($userA->getId(), $data['byUserAmounts'][0]['user']['id']);
        self::assertSame(-3500, $data['byUserAmounts'][0]['amounts'][0]['amount']);
    }

    #[TestDox('Balance GET: settled counterparty is omitted')]
    public function testSettledCounterpartyIsOmitted(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser();

        $this->createExpense($userA, [[$userA, 5000], [$userB, 5000]]);
        $this->createExpense($userB, [[$userA, 5000], [$userB, 5000]]);

        $this->signIn($userA);
        $this->sendRequest();

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->getResponseData();

        self::assertSame([], $data['byUserAmounts']);
        self::assertSame([], $data['totalAmounts']);
    }

    #[TestDox('Balance GET: currencies are kept apart')]
    public function testBalancePerCurrency(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser();

        $this->createExpense($userA, [[$userA, 4000], [$userB, 6000]], $this->currency);
        $this->createExpense($userA, [[$userA, 1000], [$userB, 2000]], $this->otherCurrency);

        $this->signIn($userA);
        $this->sendRequest();

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->getResponseData();

        self::assertCount(1, $data['byUserAmounts']);
        self::assertCount(2, $data['byUserAmounts'][0]['amounts']);
        self::assertCount(2, $data['totalAmounts']);

        $amountByCode = [];
        foreach ($data['totalAmounts'] as $total) {
            $amountByCode[$total['currency']['code']] = $total['amount'];
        }

        self::assertSame(6000, $amountByCode[$this->currency->getCode()]);
        self::assertSame(2000, $amountByCode[$this->otherCurrency->getCode()]);
    }

    #[TestDox('Balance GET: an expense paid by somebody else does not link its participants')]
    public function testThirdPartyPayerIsNotShared(): void
    {
        $payer = $this->createUser(flush: false);
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser();

        $this->createExpense($payer, [[$payer, 2000], [$userA, 4000], [$userB, 4000]]);

        $this->signIn($userA);
        $this->sendRequest();

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->getResponseData();

        self::assertCount(1, $data['byUserAmounts']);
        self::assertSame($payer->getId(), $data['byUserAmounts'][0]['user']['id']);
        self::assertSame(-4000, $data['byUserAmounts'][0]['amounts'][0]['amount']);
    }

    #[TestDox('Balance GET: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param array<int, array{0: User, 1: int}> $splits
     */
    private function createExpense(User $payer, array $splits, ?Currency $currency = null): void
    {
        $total = 0;
        foreach ($splits as [, $splitAmount]) {
            $total += $splitAmount;
        }

        $expense = new SeExpense(
            paidByUser: $payer,
            createdByUser: $payer,
            category: $this->category,
            amount: $total,
            title: 'Balance test expense',
            currency: $currency ?? $this->currency,
            expenseDate: new DateTimeImmutable(),
        );

        foreach ($splits as [$user, $splitAmount]) {
            $expense->addSplit(new SeExpenseSplit($expense, $user, $splitAmount));
        }

        $this->expenseRepo->save($expense, true);
    }

    private function sendRequest(): void
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_balance_get'),
        );
    }
}
