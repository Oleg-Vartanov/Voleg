<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Entity\UserContact;
use App\User\Repository\UserContactRepository;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('User contacts')]
class UserContactPostActionTest extends ApiTestCase
{
    #[TestDox('POST: success')]
    public function testSuccess(): void
    {
        $repository = $this->getService(UserContactRepository::class);

        $user = $this->createUser();
        $contact = $this->createUser();

        $this->signIn($user);
        $this->sendRequest($user->getId(), $contact->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertNotEmpty($repository->findOneByUsers($user, $contact));
    }

    #[TestDox('POST: user not found')]
    public function testUserNotFound(): void
    {
        $admin = $this->createUser(isAdmin: true);
        $contact = $this->createUser();

        $this->signIn($admin);
        $this->sendRequest(0, $contact->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('POST: contact not found')]
    public function testContactNotFound(): void
    {
        $user = $this->createUser();

        $this->signIn($user);
        $this->sendRequest($user->getId(), 0);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('POST: same user error')]
    public function testSameUserError(): void
    {
        $user = $this->createUser();

        $this->signIn($user);
        $this->sendRequest($user->getId(), $user->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[TestDox('POST: contact already exists error')]
    public function testContactAlreadyExistsError(): void
    {
        $user = $this->createUser();
        $contact = $this->createUser();

        $this->getService(UserContactRepository::class)
             ->save(new UserContact($user, $contact), true);

        $this->signIn($user);
        $this->sendRequest($user->getId(), $contact->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[TestDox('POST: no access')]
    public function testNoAccess(): void
    {
        $user = $this->createUser();
        $contact = $this->createUser();

        $this->signIn($this->createUser());
        $this->sendRequest($user->getId(), $contact->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('POST: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest(1, 1);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(int $id, int $contactId): void
    {
        $this->client->request(
            method: Request::METHOD_POST,
            uri: $this->router->generate('user_contact_post', [
                'id' => $id,
                'contactId' => $contactId,
            ]),
        );
    }
}