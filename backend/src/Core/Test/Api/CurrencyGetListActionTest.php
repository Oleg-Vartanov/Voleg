<?php

namespace App\Core\Test\Api;

use App\Core\Repository\CurrencyRepository;
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

        $expectedCount = count($this->getService(CurrencyRepository::class)->findAll());
        self::assertCount($expectedCount, $data);
        $usdIndex = array_search('USD', array_column($data, 'code'), true);
        self::assertNotFalse($usdIndex);
        self::assertSame('USD', $data[$usdIndex]['code']);
        self::assertSame('US Dollar', $data[$usdIndex]['name']);
    }

    private function sendRequest(): void
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('currency_get_list'),
        );
    }
}
