<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Test\ApiTestCase;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeConnectionDeleteActionTest extends ApiTestCase
{
    /**
     * @throws ORMException
     */
    #[TestDox('Connection DELETE: success')]
    public function testSuccess(): void
    {
        $em = $this->getService(EntityManagerInterface::class);
        $repo = $this->getService(SeConnectionRepository::class);

        $userA = $em->getReference('user1', User::class);
        $userB = $em->getReference('user2', User::class);

        $connection = $repo->findOneByUsers($userA, $userB);

        $this->signIn($userA);
        $this->sendRequest($connection->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertNull($repo->findOneByUsers($userA, $userB));
    }

    #[TestDox('Connection DELETE: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest(0);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(int $id): void
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_connection_delete', ['id' => $id]),
        );
    }
}
