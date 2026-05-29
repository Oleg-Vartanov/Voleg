<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;

#[TestDox('User')]
class UserGetListActionTest extends ApiTestCase
{
    #[TestDox('User GET list: success')]
    public function testUserGetListSuccess(): void
    {
        $user = $this->createUser();

        $this->sendRequest(
            $user->getTag()
        );

        self::assertResponseIsSuccessful();
        self::assertEquals($user->getTag(), $this->getResponseData()[0]['tag']);
    }

    private function sendRequest(?string $tag): void
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate('user_get_list'),
            parameters: [
                'tag' => $tag,
                'offset' => 0,
                'limit' => 100,
            ]
        );
    }
}
