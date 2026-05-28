<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Test\ApiTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split expense categories')]
class SeCategoryGetListActionTest extends ApiTestCase
{
    #[TestDox('GET list: success')]
    public function testSuccess(): void
    {
        $this->signIn($this->createUser());
        $response = $this->sendRequest();

        $data = json_decode($response->getContent(), true);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertCount(5, $data);

        /** @see \App\SplitExpense\DataFixture\SeCategoryFixture */
        self::assertSame('other1', $data[0]['tag']);
        self::assertSame('Other1', $data[0]['title']);
        self::assertSame('other5', $data[4]['tag']);
        self::assertSame('Other5', $data[4]['title']);
    }

    #[TestDox('GET list: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(): Response
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_category_get_list'),
        );

        return $this->client->getResponse();
    }
}
