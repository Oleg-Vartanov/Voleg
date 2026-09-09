<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Entity\Currency;
use App\Core\Repository\CurrencyRepository;
use App\Core\Test\ApiTestCase;
use App\SplitExpense\Repository\SeAdjustmentRepository;
use App\SplitExpense\Test\Trait\SplitExpenseTestTrait;
use App\User\Entity\User;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeAdjustmentPostActionTest extends ApiTestCase
{
    use SplitExpenseTestTrait;

    private SeAdjustmentRepository $adjustmentRepo;
    private Currency $currency;

    public function setUp(): void
    {
        parent::setUp();

        $this->adjustmentRepo = $this->getService(SeAdjustmentRepository::class);
        $this->currency = $this->getService(CurrencyRepository::class)->list(0, 1)[0];
    }

    #[TestDox('Adjustment POST: creates an adjustment')]
    public function testSuccess(): void
    {
        [$userA, $userB] = $this->connectedUsers();

        $this->signIn($userA);
        $this->sendRequest($this->payload($userB));

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = $this->getResponseData();

        self::assertNotNull($this->adjustmentRepo->find($data['id']));
        self::assertSame($userA->getId(), $data['createdByUser']['id']);
        self::assertSame($userB->getId(), $data['otherUser']['id']);
        self::assertSame(5000, $data['amount']);
        self::assertSame('2026-05-27', substr((string) $data['adjustmentDate'], 0, 10));
        self::assertSame('Settled in cash', $data['description']);
    }

    #[TestDox('Adjustment POST: stores a negative amount')]
    public function testNegativeAmount(): void
    {
        [$userA, $userB] = $this->connectedUsers();

        $this->signIn($userA);
        $this->sendRequest($this->payload($userB, ['amount' => -5000]));

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertSame(-5000, $this->getResponseData()['amount']);
    }

    #[TestDox('Adjustment POST: zero amount is rejected')]
    public function testZeroAmountIsRejected(): void
    {
        [$userA, $userB] = $this->connectedUsers();

        $this->signIn($userA);
        $this->sendRequest($this->payload($userB, ['amount' => 0]));

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Adjustment POST: same other user is rejected')]
    public function testSameUserIsRejected(): void
    {
        $user = $this->createUser();

        $this->signIn($user);
        $this->sendRequest($this->payload($user));

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Adjustment POST: unconnected user is rejected')]
    public function testUnconnectedUserIsRejected(): void
    {
        $userA = $this->createUser(flush: false);
        $userB = $this->createUser();

        $this->signIn($userA);
        $this->sendRequest($this->payload($userB));

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[TestDox('Adjustment POST: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest([]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
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
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function payload(User $otherUser, array $overrides = []): array
    {
        return array_merge([
            'otherUserId' => $otherUser->getId(),
            'amount' => 5000,
            'currencyId' => $this->currency->getId(),
            'adjustmentDate' => '2026-05-27',
            'description' => 'Settled in cash',
        ], $overrides);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function sendRequest(array $payload): void
    {
        $this->client->jsonRequest(
            method: Request::METHOD_POST,
            uri: $this->router->generate('se_adjustment_post'),
            parameters: $payload,
        );
    }
}
