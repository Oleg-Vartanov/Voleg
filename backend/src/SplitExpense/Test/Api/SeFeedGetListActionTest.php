<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Entity\Currency;
use App\Core\Repository\CurrencyRepository;
use App\Core\Test\ApiTestCase;
use App\SplitExpense\Entity\SeAdjustment;
use App\SplitExpense\Entity\SeCategory;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\Entity\SeExpenseSplit;
use App\SplitExpense\Repository\SeAdjustmentRepository;
use App\SplitExpense\Repository\SeCategoryRepository;
use App\SplitExpense\Repository\SeExpenseRepository;
use App\SplitExpense\Test\Trait\SplitExpenseTestTrait;
use App\User\Entity\User;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;

#[TestDox('Split Expense')]
class SeFeedGetListActionTest extends ApiTestCase
{
    use SplitExpenseTestTrait;

    private SeExpenseRepository $expenseRepo;
    private SeAdjustmentRepository $adjustmentRepo;
    private SeCategory $category;
    private Currency $currency;

    public function setUp(): void
    {
        parent::setUp();

        $this->expenseRepo = $this->getService(SeExpenseRepository::class);
        $this->adjustmentRepo = $this->getService(SeAdjustmentRepository::class);
        $this->category = $this->getService(SeCategoryRepository::class)->find(SeCategory::DEFAULT_ID);
        $this->currency = $this->getService(CurrencyRepository::class)->list(0, 1)[0];
    }

    /**
     * @throws DateMalformedStringException
     */
    #[TestDox('Feed GET list: returns expenses and adjustments of the current user, newest first')]
    public function testSuccess(): void
    {
        [$userA, $userB] = $this->connectedUsers();
        $outsider = $this->createUser();

        $oldExpense = $this->createExpense($userA, $userB, '2026-05-01');
        $oldAdjustment = $this->createAdjustment($userB, $userA, '2026-05-10');
        $sameDayExpense = $this->createExpense($userB, $userA, '2026-05-20');
        $sameDayAdjustment = $this->createAdjustment($userA, $userB, '2026-05-20');
        $this->createExpense($outsider, $userB, '2026-05-15');
        $this->createAdjustment($outsider, $userB, '2026-05-15');

        $this->signIn($userA);
        $this->requestFeed();

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            [
                ['adjustment', $sameDayAdjustment->getId()],
                ['expense', $sameDayExpense->getId()],
                ['adjustment', $oldAdjustment->getId()],
                ['expense', $oldExpense->getId()],
            ],
            $this->responseTypesAndIds(),
        );
        self::assertNull($this->getResponseData()[0]['expense']);
        self::assertSame('4', $this->client->getResponse()->headers->get('X-Total-Count'));
    }

    /**
     * @throws DateMalformedStringException
     */
    #[TestDox('Feed GET list: filters by record type')]
    public function testTypesFilter(): void
    {
        [$userA, $userB] = $this->connectedUsers();
        $this->createExpense($userA, $userB, '2026-05-01');
        $adjustment = $this->createAdjustment($userA, $userB, '2026-05-02');

        $this->signIn($userA);
        $this->requestFeed(['types' => ['adjustment']]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame([['adjustment', $adjustment->getId()]], $this->responseTypesAndIds());
        self::assertSame('1', $this->client->getResponse()->headers->get('X-Total-Count'));
    }

    /**
     * @throws DateMalformedStringException
     */
    #[TestDox('Feed GET list: paginates across record types')]
    public function testPagination(): void
    {
        [$userA, $userB] = $this->connectedUsers();
        $this->createExpense($userA, $userB, '2026-05-04');
        $adjustment = $this->createAdjustment($userA, $userB, '2026-05-03');
        $expense = $this->createExpense($userA, $userB, '2026-05-02');
        $this->createAdjustment($userA, $userB, '2026-05-01');

        $this->signIn($userA);
        $this->requestFeed(['offset' => 1, 'limit' => 2]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            [['adjustment', $adjustment->getId()], ['expense', $expense->getId()]],
            $this->responseTypesAndIds(),
        );
        self::assertSame('4', $this->client->getResponse()->headers->get('X-Total-Count'));
    }

    /**
     * @throws DateMalformedStringException
     */
    #[TestDox('Feed GET list: loads relations up front, so queries do not grow with the page')]
    public function testNoLazyLoading(): void
    {
        [$smallFeedUser, $partner] = $this->connectedUsers();
        $this->createExpense($smallFeedUser, $partner, '2026-05-01');
        $this->createAdjustment($smallFeedUser, $partner, '2026-05-01');

        $largeFeedUser = $this->createUser();
        for ($i = 1; $i <= 4; $i++) {
            $otherPartner = $this->createUser();
            $this->createExpense($otherPartner, $largeFeedUser, "2026-05-0$i");
            $this->createAdjustment($otherPartner, $largeFeedUser, "2026-05-0$i");
        }

        $smallFeedQueries = $this->feedQueryCount($smallFeedUser);
        $largeFeedQueries = $this->feedQueryCount($largeFeedUser);

        self::assertCount(8, $this->getResponseData());
        self::assertSame($smallFeedQueries, $largeFeedQueries);
    }

    #[TestDox('Feed GET list: rejects an unknown record type')]
    public function testInvalidType(): void
    {
        $this->signIn($this->createUser());
        $this->requestFeed(['types' => ['unknown']]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Feed GET list: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->requestFeed();

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param array<string, mixed> $query
     */
    private function requestFeed(array $query = []): void
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_feed_get_list', $query),
        );
    }

    private function feedQueryCount(User $user): int
    {
        $this->signIn($user);
        $this->client->enableProfiler();
        $this->requestFeed();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $profile = $this->client->getProfile();
        self::assertInstanceOf(Profile::class, $profile);
        $collector = $profile->getCollector('db');
        self::assertInstanceOf(DoctrineDataCollector::class, $collector);

        return $collector->getQueryCount();
    }

    /**
     * @return list<array{0: string, 1: int}>
     */
    private function responseTypesAndIds(): array
    {
        /** @var list<array{type: string, expense: array{id: int}|null, adjustment: array{id: int}|null}> $data */
        $data = $this->getResponseData();

        return array_map(
            static fn (array $item): array => [$item['type'], $item[$item['type']]['id']],
            $data,
        );
    }

    /**
     * @return array{0: User, 1: User}
     */
    private function connectedUsers(): array
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser(flush: false);
        $this->createConnection($userA, $userB);

        return [$userA, $userB];
    }

    /**
     * @throws DateMalformedStringException
     */
    private function createExpense(User $paidBy, User $splitWith, string $date): SeExpense
    {
        $expense = new SeExpense(
            paidByUser: $paidBy,
            createdByUser: $paidBy,
            category: $this->category,
            amount: 10000,
            title: 'Dinner',
            currency: $this->currency,
            expenseDate: new DateTimeImmutable($date),
        );
        $expense->addSplit(new SeExpenseSplit($expense, $paidBy, 5000));
        $expense->addSplit(new SeExpenseSplit($expense, $splitWith, 5000));
        $this->expenseRepo->save($expense, true);

        return $expense;
    }

    /**
     * @throws DateMalformedStringException
     */
    private function createAdjustment(User $createdBy, User $otherUser, string $date): SeAdjustment
    {
        $adjustment = new SeAdjustment(
            createdByUser: $createdBy,
            otherUser: $otherUser,
            amount: 5000,
            currency: $this->currency,
            adjustmentDate: new DateTimeImmutable($date),
        );
        $this->adjustmentRepo->save($adjustment, true);

        return $adjustment;
    }
}
