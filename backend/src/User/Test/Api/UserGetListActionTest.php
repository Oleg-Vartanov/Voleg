<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Test\Trait\UserTestTrait;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('User')]
class UserGetListActionTest extends ApiTestCase
{
    use UserTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->bootUserTest();
    }

    #[TestDox('User GET list: success')]
    public function testUserGetListSuccess(): void
    {
        $user = $this->createUser();

        $response = $this->sendRequest(
            $user->getTag()
        );

        $data = json_decode($response->getContent(), true);
        $responseUser = $data[0];

        $this->assertResponseIsSuccessful();
        $this->assertEquals($user->getTag(), $responseUser['tag']);
    }

    private function sendRequest(
        ?string $tag,
        int $offset = 0,
        int $limit = 100,
    ): Response {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('user_get_list'),
            parameters: [
                'tag' => $tag,
                'offset' => $offset,
                'limit' => $limit,
            ]
        );

        return $this->client->getResponse();
    }
}
