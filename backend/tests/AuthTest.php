<?php

namespace App\Tests;

use App\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthTest extends WebTestCase
{
    private KernelBrowser $client;
    private AuthService $authService;
    private EntityManagerInterface $entityManager;
    private UserFactory $userFactory;
    private UserRepository $userRepository;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->authService = $this->getContainer()->get(AuthService::class);
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $this->userFactory = $this->getContainer()->get(UserFactory::class);
        $this->userRepository = $this->getContainer()->get(UserRepository::class);
    }

    /** @test */
    public function signUpSuccess(): void
    {
        $lastUserId = $this->userRepository->findOneBy([], ['id' => 'desc'])?->getId() ?? 1;

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

    /** @test */
    public function verifyUser(): void
    {
        // Create user.
        $lastUserId = $this->userRepository->findOneBy([], ['id' => 'desc'])?->getId() ?? 1;
        $user = $this->userFactory->create([
            'email' => 'user'.($lastUserId + 1).'@example.com',
            'password' => '!Qwerty1',
            'displayName' => 'John Doe',
        ]);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Send verify email.
        $this->authService->sendVerificationEmail($user);
        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains($email, $user->getVerificationCode());

        // Verify email via link from an email.
        $verificationLink = $this->authService->createVerificationLink($user);
        $this->client->request(method: 'GET', uri: $verificationLink);
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(1, $user->isVerified());
    }

    private function signUpRequest(string $bodyJson): Response
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request(method: 'POST', uri: '/sign-up/create', server: $headers, content: $bodyJson);

        return $this->client->getResponse();
    }
}
