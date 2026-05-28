<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeConnectionGetListActionTest extends ApiTestCase
{
    /**
     * @throws ORMException
     */
    #[TestDox('Connection GET list: success')]
    public function testSuccess(): void
    {
        $em = $this->getService(EntityManagerInterface::class);
        $user = $em->getReference('user1', User::class);

        $this->signIn($user);
        $response = $this->sendRequest();

        $data = json_decode($response->getContent(), true);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertCount(9, $data);
    }

    #[TestDox('Connection GET list: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(): Response
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_connection_get_list'),
        );

        return $this->client->getResponse();
    }
}
