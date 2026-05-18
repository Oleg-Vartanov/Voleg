<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Entity\UserContact;
use App\User\Repository\UserContactRepository;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('User contacts')]
class UserContactGetListActionTest extends ApiTestCase
{
    #[TestDox('GET list: success')]
    public function testSuccess(): void
    {
        $repo = $this->getService(UserContactRepository::class);
        $count = 5;

        $users = [];
        foreach (range(1, $count) as $i) {
            $users[$i] = $this->createUser(flush: false);
        }
        $admin = $this->createUser(isAdmin: true);

        foreach (range(1, $count) as $i) {
            $repo->save(new UserContact($admin, $users[$i]));
        }
        $repo->flush();


        $this->signIn($admin);
        $response = $this->sendRequest($admin->getId());

        $data = json_decode($response->getContent(), true);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertCount($count, $data);
    }

    #[TestDox('GET list: user not found')]
    public function testUserNotFound(): void
    {
        $admin = $this->createUser(isAdmin: true);

        $this->signIn($admin);
        $this->sendRequest(0);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('GET list: no access')]
    public function testNoAccess(): void
    {
        $this->signIn($this->createUser());
        $this->sendRequest($this->createUser()->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('GET list: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest(0);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(int $id): Response
    {
        $this->client->request(
            method: Request::METHOD_GET,
            uri: $this->router->generate(
                name: 'user_contact_get_list',
                parameters: ['id' => $id],
            ),
        );

        return $this->client->getResponse();
    }
}