<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Entity\Currency;
use App\Core\Repository\CurrencyRepository;
use App\Core\Test\ApiTestCase;
use App\SplitExpense\Entity\SeAdjustment;
use App\SplitExpense\Repository\SeAdjustmentRepository;
use App\SplitExpense\Test\Trait\SplitExpenseTestTrait;
use App\User\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeAdjustmentPutActionTest extends ApiTestCase
{
    use SplitExpenseTestTrait;

    private SeAdjustmentRepository $adjustmentRepo;
    private EntityManagerInterface $em;
    private Currency $currency;

    public function setUp(): void
    {
        parent::setUp();

        $this->adjustmentRepo = $this->getService(SeAdjustmentRepository::class);
        $this->em = $this->getService(EntityManagerInterface::class);
        $this->currency = $this->getService(CurrencyRepository::class)->list(0, 1)[0];
    }

    #[TestDox('Adjustment PUT: replaces the whole adjustment')]
    public function testSuccess(): void
    {
        [$userA, $userB] = $this->connectedUsers();
        $adjustment = $this->createAdjustment($userA, $userB);

        $this->signIn($userA);
        $this->sendRequest($adjustment->getId(), $this->payload($userB, [
            'description' => 'Updated description',
            'amount' => 20000,
            'adjustmentDate' => '2026-06-01',
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame('Updated description', $this->getResponseData()['description']);

        $this->em->clear();
        $updated = $this->adjustmentRepo->find($adjustment->getId());

        self::assertNotNull($updated);
        self::assertSame('Updated description', $updated->getDescription());
        self::assertSame(20000, $updated->getAmount());
        self::assertSame('2026-06-01', $updated->getAdjustmentDate()->format('Y-m-d'));
        self::assertSame($userB->getId(), $updated->getOtherUser()->getId());
    }

    #[TestDox('Adjustment PUT: omitted description is cleared')]
    public function testOmittedFieldIsReplaced(): void
    {
        [$userA, $userB] = $this->connectedUsers();
        $adjustment = $this->createAdjustment($userA, $userB);

        $payload = $this->payload($userB);
        unset($payload['description']);

        $this->signIn($userA);
        $this->sendRequest($adjustment->getId(), $payload);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->em->clear();
        self::assertNull($this->adjustmentRepo->find($adjustment->getId())->getDescription());
    }

    #[TestDox('Adjustment PUT: incomplete payload is rejected')]
    public function testIncompletePayloadIsRejected(): void
    {
        [$userA, $userB] = $this->connectedUsers();
        $adjustment = $this->createAdjustment($userA, $userB);

        $this->signIn($userA);
        $this->sendRequest($adjustment->getId(), ['description' => 'Updated description']);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->em->clear();
        self::assertSame('Original description', $this->adjustmentRepo->find($adjustment->getId())->getDescription());
    }

    #[TestDox('Adjustment PUT: other user is not replaced')]
    public function testOtherUserIsNotReplaced(): void
    {
        [$userA, $userB] = $this->connectedUsers();
        $userC = $this->createUser();
        $this->createConnection($userA, $userC);
        $adjustment = $this->createAdjustment($userA, $userB);

        $this->signIn($userA);
        $this->sendRequest($adjustment->getId(), $this->payload($userC, [
            'description' => 'Updated description',
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->em->clear();
        $stored = $this->adjustmentRepo->find($adjustment->getId());
        self::assertSame($userB->getId(), $stored->getOtherUser()->getId());
        self::assertSame('Updated description', $stored->getDescription());
    }

    #[TestDox('Adjustment PUT: access denied')]
    public function testAccessDenied(): void
    {
        [$userA, $userB] = $this->connectedUsers();
        $outsider = $this->createUser();
        $adjustment = $this->createAdjustment($userA, $userB);

        $this->signIn($outsider);
        $this->sendRequest($adjustment->getId(), $this->payload($userB));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('Adjustment PUT: adjustment not found')]
    public function testNotFound(): void
    {
        [$userA, $userB] = $this->connectedUsers();

        $this->signIn($userA);
        $this->sendRequest(999999, $this->payload($userB));

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('Adjustment PUT: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest(0, []);

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

    private function createAdjustment(User $createdBy, User $otherUser): SeAdjustment
    {
        $adjustment = new SeAdjustment(
            createdByUser: $createdBy,
            otherUser: $otherUser,
            amount: 5000,
            currency: $this->currency,
            adjustmentDate: new DateTimeImmutable('2026-05-27'),
            description: 'Original description',
        );
        $this->adjustmentRepo->save($adjustment, true);

        return $adjustment;
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
            'description' => 'Original description',
        ], $overrides);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function sendRequest(int $id, array $payload): void
    {
        $this->client->jsonRequest(
            method: Request::METHOD_PUT,
            uri: $this->router->generate('se_adjustment_put', ['id' => $id]),
            parameters: $payload,
        );
    }
}
