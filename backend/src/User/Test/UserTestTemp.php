<?php

namespace App\User\Test;

use App\User\Entity\User;
use App\User\Factory\UserFactory;
use App\User\Repository\UserRepository;
use App\User\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

// TODO
#[TestDox('User Controller')]
class UserTestTemp extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private UserFactory $userFactory;
    private UserRepository $userRepository;
    private RouterInterface $router;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->authService = $this->getContainer()->get(AuthService::class);
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $this->userFactory = $this->getContainer()->get(UserFactory::class);
        $this->userRepository = $this->getContainer()->get(UserRepository::class);
        $this->router = $this->getContainer()->get(RouterInterface::class);
    }


    #[TestDox('User list action')]
    public function list(): void
    {
        $url = $this->router->generate('user_list');
        $this->client->request(method: 'GET', uri: $url);
        $this->assertResponseIsSuccessful();
    }


    #[TestDox('User show action')]
    public function show(): void
    {
        $user = $this->createUser();
        $url = $this->router->generate('user_show', ['id' => $user->getId()]);
        $this->client->request(method: 'GET', uri: $url);
        $this->assertResponseIsSuccessful();
    }


    #[TestDox('User controller forbidden')]
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


    #[TestDox('User delete action')]
    public function delete():void
    {
        $user = $this->createUser(true);
        $token = $this->signIn($user);

        $url = $this->router->generate('user_delete', ['id' => $user->getId()]);
        $this->client->request('DELETE', $url, server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }


    #[TestDox('User patch action')]
    public function patch():void
    {
        $user = $this->createUser(true);
        $token = $this->signIn($user);

        $url = $this->router->generate('user_patch', ['id' => $user->getId()]);
        $this->client->request('PATCH', $url, server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token], content: json_encode([]));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }


    #[TestDox('User sign in action')]
    protected function signIn(UserTestTemp $user)
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

    private function createUser(bool $isAdmin = false): UserTestTemp
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
