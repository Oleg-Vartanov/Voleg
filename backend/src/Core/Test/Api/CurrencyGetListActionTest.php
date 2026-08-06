<?php

namespace App\Core\Test\Api;

use App\Core\Test\ApiTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Core: Currency')]
class CurrencyGetListActionTest extends ApiTestCase
{
    #[TestDox('GET list: success')]
    public function testSuccess(): void
    {
        $this->signIn($this->createUser());
        $this->sendRequest();

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->getResponseData();
        /** @see \App\Core\DataFixture\CurrencyFixture */
        self::assertCount(50, $data);
        self::assertSame('C1', $data[0]['code']);
        self::assertSame('Currency 1', $data[0]['name']);
        self::assertSame('C10', $data[1]['code']);
    }

    private function sendRequest(): void
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('currency_get_list'),
        );
    }
}
