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
        $response = $this->sendRequest();

        $data = json_decode($response->getContent(), true);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        /** @see \App\Core\DataFixture\CurrencyFixture */
        self::assertCount(52, $data);
        self::assertSame('USD', $data[0]['code']);
        self::assertSame('EUR', $data[1]['code']);
    }

    private function sendRequest(): Response
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('currency_get_list'),
        );

        return $this->client->getResponse();
    }
}
