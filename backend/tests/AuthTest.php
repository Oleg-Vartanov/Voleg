<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /** @test */
    public function signUpSuccess(): void
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getContainer()->get(UserRepository::class);
        $lastUserId = $userRepo->findOneBy([], ['id' => 'desc'])?->getId() ?? 1;

        $testUser = [
            'email' => 'user'.($lastUserId + 1).'@example.com',
            'password' => '!Qwerty1',
            'displayName' => 'John Doe',
        ];

        $response = $this->signUpRequest(json_encode($testUser));

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains($email, $testUser['displayName']);
    }

    /** @test */
    public function signUpValidationError(): void
    {
        $response = $this->signUpRequest(json_encode([
            'email' => 'john.doe',
            'password' => 'qwert y',
            'displayName' => '',
        ]));

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    private function signUpRequest(string $bodyJson): Response
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request(method: 'POST', uri: '/sign-up/create', server: $headers, content: $bodyJson);

        return $this->client->getResponse();
    }
}
