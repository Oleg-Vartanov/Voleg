<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Test\ApiTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Split Expense')]
class SeCategoryGetListActionTest extends ApiTestCase
{
    #[TestDox('Categories GET list: success')]
    public function testSuccess(): void
    {
        $this->signIn($this->createUser());
        $this->sendRequest();

        $data = $this->getResponseData();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertCount(6, $data);

        /** @see \App\SplitExpense\DataFixture\SeCategoryFixture */
        self::assertSame('other', $data[0]['tag']);
        self::assertSame('Other', $data[0]['title']);
        self::assertSame('other4', $data[4]['tag']);
        self::assertSame('Other4', $data[4]['title']);
    }

    #[TestDox('Categories GET list: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest();
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(): void
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('se_category_get_list'),
        );
    }
}
