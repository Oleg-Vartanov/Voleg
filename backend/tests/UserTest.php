<?php

namespace App\Tests;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class UserTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private UserFactory $userFactory;
    private UserRepository $userRepository;
    private RouterInterface $router;

    public function setUp(): void
    {
        $this->client = static::createClient([

        ]);
        $this->authService = $this->getContainer()->get(AuthService::class);
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $this->userFactory = $this->getContainer()->get(UserFactory::class);
        $this->userRepository = $this->getContainer()->get(UserRepository::class);
        $this->router = $this->getContainer()->get(RouterInterface::class);
    }

    /** @test */
    public function list(): void
    {
        $url = $this->router->generate('user_list');
        $this->client->request(method: 'GET', uri: $url);
        $this->assertResponseIsSuccessful();
    }

    /** @test */
    public function show(): void
    {
        $user = $this->createUser();
        $url = $this->router->generate('user_show', ['id' => $user->getId()]);
        $this->client->request(method: 'GET', uri: $url);
        $this->assertResponseIsSuccessful();
    }

    /** @test */
    public function forbidden(): void
    {
        $userToProcess = $this->createUser();
        $signedInUser = $this->createUser();
        $token = $this->signIn($signedInUser);

        $url = $this->router->generate('user_delete', ['id' => $userToProcess->getId()]);
        $this->client->request('DELETE', $url, server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $url = $this->router->generate('user_patch', ['id' => $userToProcess->getId()]);
        $this->client->request('PATCH', $url, server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token], content: json_encode([]));
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    /** @test */
    public function delete():void
    {
        $user = $this->createUser(true);
        $token = $this->signIn($user);

        $url = $this->router->generate('user_delete', ['id' => $user->getId()]);
        $this->client->request('DELETE', $url, server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    /** @test */
    public function patch():void
    {
        $user = $this->createUser(true);
        $token = $this->signIn($user);

        $url = $this->router->generate('user_patch', ['id' => $user->getId()]);
        $this->client->request('PATCH', $url, server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token], content: json_encode([]));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    protected function signIn(User $user)
    {
        $this->client->jsonRequest(
            'POST',
            $this->router->generate('sign_in'),
            [
                'email' => $user->getUserIdentifier(),
                'password' => '!Qwerty1',
            ]
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);

        return $data['token'];
    }

    private function createUser(bool $isAdmin = false): User
    {
        $lastUserId = $this->userRepository->findOneBy([], ['id' => 'desc'])?->getId() ?? 0;

        $user = $this->userFactory->create([
            'email' => 'user'.($lastUserId + 1).'@example.com',
            'password' => '!Qwerty1',
            'displayName' => 'John Doe',
        ]);

        $user->setVerified(true);
        if ($isAdmin) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
