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
use App\SplitExpense\Repository\SeExpenseSplitRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeExpensePutActionTest extends ApiTestCase
{
    private SeExpenseRepository $expenseRepo;
    private SeExpenseSplitRepository $splitRepo;
    private UserRepository $userRepo;
    private EntityManagerInterface $em;
    private SeCategory $category;
    private Currency $currency;

    public function setUp(): void
    {
        parent::setUp();

        $this->expenseRepo = $this->getService(SeExpenseRepository::class);
        $this->splitRepo = $this->getService(SeExpenseSplitRepository::class);
        $this->userRepo = $this->getService(UserRepository::class);
        $this->em = $this->getService(EntityManagerInterface::class);
        $this->category = $this->getService(SeCategoryRepository::class)->find(SeCategory::DEFAULT_ID);
        $this->currency = $this->getService(CurrencyRepository::class)->list(0, 1)[0];
    }

    #[TestDox('Expense PUT: replaces the whole expense')]
    public function testSuccess(): void
    {
        $userA = $this->userRepo->findByUsername('user1');
        $userB = $this->userRepo->findByUsername('user6');
        $userC = $this->userRepo->findByUsername('user7');
        $expense = $this->createExpense($userA, $userB);

        $this->signIn($userA);
        $this->sendRequest($expense->getId(), $this->payload($userA, $userC, [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'amount' => 20000,
            'expenseDate' => '2026-06-01',
            'splits' => [
                ['userId' => $userA->getId(), 'amount' => 12000],
                ['userId' => $userC->getId(), 'amount' => 8000],
            ],
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame('Updated title', $this->getResponseData()['title']);

        $this->em->clear();
        $updated = $this->expenseRepo->find($expense->getId());

        self::assertNotNull($updated);
        self::assertSame('Updated title', $updated->getTitle());
        self::assertSame('Updated description', $updated->getDescription());
        self::assertSame(20000, $updated->getAmount());
        self::assertSame('2026-06-01', $updated->getExpenseDate()->format('Y-m-d'));
        self::assertEqualsCanonicalizing(
            [$userA->getId(), $userC->getId()],
            $this->splitUserIds($updated),
        );
    }

    #[TestDox('Expense PUT: omitted description is cleared')]
    public function testOmittedFieldIsReplaced(): void
    {
        $userA = $this->userRepo->findByUsername('user1');
        $userB = $this->userRepo->findByUsername('user6');
        $expense = $this->createExpense($userA, $userB);

        $payload = $this->payload($userA, $userB);
        unset($payload['description']);

        $this->signIn($userA);
        $this->sendRequest($expense->getId(), $payload);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->em->clear();
        self::assertNull($this->expenseRepo->find($expense->getId())->getDescription());
    }

    #[TestDox('Expense PUT: incomplete payload is rejected')]
    public function testIncompletePayloadIsRejected(): void
    {
        $userA = $this->userRepo->findByUsername('user1');
        $userB = $this->userRepo->findByUsername('user6');
        $expense = $this->createExpense($userA, $userB);

        $this->signIn($userA);
        $this->sendRequest($expense->getId(), ['title' => 'Updated title']);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->em->clear();
        self::assertSame('Original title', $this->expenseRepo->find($expense->getId())->getTitle());
    }

    #[TestDox('Expense PUT: invalid splits keep the stored ones')]
    public function testInvalidSplitsRollBack(): void
    {
        $userA = $this->userRepo->findByUsername('user1');
        $userB = $this->userRepo->findByUsername('user6');
        $expense = $this->createExpense($userA, $userB);

        $this->signIn($userA);
        $this->sendRequest($expense->getId(), $this->payload($userA, $userB, [
            'title' => 'Updated title',
            // Sums to less than the expense amount, so applySplits() rejects it.
            'splits' => [
                ['userId' => $userA->getId(), 'amount' => 1000],
                ['userId' => $userB->getId(), 'amount' => 1000],
            ],
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->em->clear();
        $stored = $this->expenseRepo->find($expense->getId());

        self::assertSame('Original title', $stored->getTitle());
        self::assertCount(2, $this->splitRepo->findBy(['expense' => $expense->getId()]));
        self::assertSame(10000, array_sum(array_map(
            static fn (SeExpenseSplit $split): int => $split->getAmount(),
            $this->splitRepo->findBy(['expense' => $expense->getId()]),
        )));
    }

    #[TestDox('Expense PUT: split with an unconnected user is rejected')]
    public function testUnconnectedSplitUserIsRejected(): void
    {
        $userA = $this->userRepo->findByUsername('user1');
        $userB = $this->userRepo->findByUsername('user6');
        $stranger = $this->userRepo->findByUsername('user11');
        $expense = $this->createExpense($userA, $userB);

        $this->signIn($userA);
        $this->sendRequest($expense->getId(), $this->payload($userA, $stranger));

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->em->clear();
        self::assertEqualsCanonicalizing(
            [$userA->getId(), $userB->getId()],
            $this->splitUserIds($this->expenseRepo->find($expense->getId())),
        );
    }

    #[TestDox('Expense PUT: access denied')]
    public function testAccessDenied(): void
    {
        $userA = $this->userRepo->findByUsername('user1');
        $userB = $this->userRepo->findByUsername('user6');
        $outsider = $this->userRepo->findByUsername('user7');
        $expense = $this->createExpense($userA, $userB);

        $this->signIn($outsider);
        $this->sendRequest($expense->getId(), $this->payload($userA, $userB));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('Expense PUT: expense not found')]
    public function testNotFound(): void
    {
        $userA = $this->userRepo->findByUsername('user1');
        $userB = $this->userRepo->findByUsername('user6');

        $this->signIn($userA);
        $this->sendRequest(999999, $this->payload($userA, $userB));

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('Expense PUT: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest(0, []);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function createExpense(User $userA, User $userB): SeExpense
    {
        $expense = new SeExpense(
            paidByUser: $userA,
            createdByUser: $userA,
            category: $this->category,
            amount: 10000,
            title: 'Original title',
            currency: $this->currency,
            expenseDate: new DateTimeImmutable('2026-05-27'),
            description: 'Original description',
        );

        $expense->addSplit(new SeExpenseSplit($expense, $userA, 5000));
        $expense->addSplit(new SeExpenseSplit($expense, $userB, 5000));
        $this->expenseRepo->save($expense, true);

        return $expense;
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function payload(User $paidBy, User $splitWith, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Original title',
            'amount' => 10000,
            'currencyId' => $this->currency->getId(),
            'expenseDate' => '2026-05-27',
            'categoryId' => $this->category->getId(),
            'paidByUserId' => $paidBy->getId(),
            'description' => 'Original description',
            'splits' => [
                ['userId' => $paidBy->getId(), 'amount' => 5000],
                ['userId' => $splitWith->getId(), 'amount' => 5000],
            ],
        ], $overrides);
    }

    /**
     * @return int[]
     */
    private function splitUserIds(SeExpense $expense): array
    {
        $ids = array_map(
            static fn (SeExpenseSplit $split): int => $split->getUser()->getId(),
            $expense->getSplits()->toArray(),
        );
        sort($ids);

        return $ids;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function sendRequest(int $id, array $payload): void
    {
        $this->client->jsonRequest(
            method: Request::METHOD_PUT,
            uri: $this->router->generate('se_expense_put', ['id' => $id]),
            parameters: $payload,
        );
    }
}
