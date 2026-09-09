<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Entity\Currency;
use App\Core\Repository\CurrencyRepository;
use App\Core\Test\ApiTestCase;
use App\SplitExpense\Entity\SeAdjustment;
use App\SplitExpense\Repository\SeAdjustmentRepository;
use App\SplitExpense\Test\Trait\SplitExpenseTestTrait;
use App\User\Entity\User;
use DateMalformedStringException;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeAdjustmentGetListActionTest extends ApiTestCase
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

    /**
     * @throws DateMalformedStringException
     */
    #[TestDox('Adjustment GET list: returns the current user\'s adjustments')]
    public function testSuccess(): void
    {
        [$userA, $userB] = $this->connectedUsers();
        $outsider = $this->createUser();
        $otherPair = $this->createUser(flush: false);
        $this->createConnection($outsider, $otherPair);

        $this->createAdjustment($userA, $userB);
        $this->createAdjustment($userB, $userA, '2026-06-01');
        $this->createAdjustment($outsider, $otherPair);

        $this->signIn($userA);
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_adjustment_get_list'),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->getResponseData();
        self::assertCount(2, $data);
        self::assertSame('2', $this->client->getResponse()->headers->get('X-Total-Count'));
    }

    /**
     * @throws DateMalformedStringException
     */
    #[TestDox('Adjustment GET: returns one adjustment')]
    public function testGetOne(): void
    {
        [$userA, $userB] = $this->connectedUsers();
        $adjustment = $this->createAdjustment($userA, $userB);

        $this->signIn($userB);
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_adjustment_get', ['id' => $adjustment->getId()]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame($adjustment->getId(), $this->getResponseData()['id']);
    }

    /**
     * @throws DateMalformedStringException
     */
    #[TestDox('Adjustment GET: access denied')]
    public function testGetAccessDenied(): void
    {
        [$userA, $userB] = $this->connectedUsers();
        $outsider = $this->createUser();
        $adjustment = $this->createAdjustment($userA, $userB);

        $this->signIn($outsider);
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_adjustment_get', ['id' => $adjustment->getId()]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('Adjustment GET list: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_adjustment_get_list'),
        );

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
     * @throws DateMalformedStringException
     */
    private function createAdjustment(
        User $createdBy,
        User $otherUser,
        string $date = '2026-05-27',
    ): SeAdjustment {
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
