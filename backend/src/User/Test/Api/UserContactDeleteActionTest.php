<?php

namespace App\User\Test\Api;

use App\Core\Test\ApiTestCase;
use App\User\Entity\UserContact;
use App\User\Repository\UserContactRepository;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('User contacts')]
class UserContactDeleteActionTest extends ApiTestCase
{
    #[TestDox('DELETE: success')]
    public function testSuccess(): void
    {
        $repository = $this->getService(UserContactRepository::class);

        $user = $this->createUser();
        $contact = $this->createUser();

        $userContact = new UserContact($user, $contact);
        $repository->save($userContact, true);

        $this->signIn($user);
        $this->sendRequest($user->getId(), $contact->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertEmpty($repository->find($userContact->getId()));
    }

    #[TestDox('DELETE: not found')]
    public function testNotFound(): void
    {
        $user = $this->createUser();
        $contact = $this->createUser();

        $this->signIn($user);
        $this->sendRequest($user->getId(), $contact->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[TestDox('DELETE: no access')]
    public function testNoAccess(): void
    {
        $user = $this->createUser();
        $contact = $this->createUser();

        $this->signIn($this->createUser());
        $this->sendRequest($user->getId(), $contact->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[TestDox('DELETE: unauthorized')]
    public function testUnauthorized(): void
    {
        $this->sendRequest(1, 1);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function sendRequest(int $id, int $contactId): void
    {
        $this->client->request(
            method: Request::METHOD_DELETE,
            uri: $this->router->generate('user_contact_delete', [
                'id' => $id,
                'contactId' => $contactId,
            ]),
        );
    }
}