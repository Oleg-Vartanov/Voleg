<?php

namespace App\SplitExpense\Test\Api;

use App\Core\Test\ApiTestCase;
use App\SplitExpense\Repository\SeCategoryRepository;
use App\SplitExpense\Service\Seeder\SeCategorySeeder;
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

        $expectedCount = count($this->getService(SeCategoryRepository::class)->findAll());
        self::assertCount($expectedCount, $data);
        self::assertSame(16, $expectedCount);

        $otherIndex = array_search(SeCategorySeeder::DEFAULT_TAG, array_column($data, 'tag'), true);
        self::assertNotFalse($otherIndex);
        self::assertSame(SeCategorySeeder::DEFAULT_TAG, $data[$otherIndex]['tag']);
        self::assertSame('Other', $data[$otherIndex]['title']);
        self::assertSame('Bills', $data[0]['title']);
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
