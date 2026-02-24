<?php

namespace App\User\Test\Trait;

use App\User\Entity\User;
use App\User\Repository\UserRepository;
use App\User\Service\AuthService;
use App\User\Service\UserService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;

trait UserTestTrait
{
    protected const string DEFAULT_PASSWORD = '!Qwerty1';

    protected KernelBrowser $client;
    protected UserService $userService;
    protected UserRepository $userRepository;
    protected AuthService $authService;

    protected function bootUserTest(): void
    {
        $container = static::getContainer();

        $this->userService = $container->get(UserService::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->authService = $container->get(AuthService::class);
    }

    protected function createUser(
        array $userData = [],
        bool $verified = true,
    ): User {
        $lastUserId = $this->userRepository->findOneBy([], ['id' => 'desc'])?->getId() ?? 0;

        $index = $lastUserId + 1;
        $defaults = [
            'email' => 'user' . $index . '@example.com',
            'password' => self::DEFAULT_PASSWORD,
            'displayName' => 'John Doe ' . $index,
            'tag' => 'john-doe-' . $index,
            'roles' => [],
        ];
        $userData = array_merge($defaults, $userData);

        $user = $this->userService->create(
            $userData['email'],
            $userData['password'],
            $userData['displayName'],
            $userData['tag'],
            $userData['roles'],
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        if ($verified) {
            $this->authService->verifyUser($user->getVerificationCode(), $user);
        }

        return $user;
    }

    protected function signIn(User $user): void
    {
        $this->client->request(
            method: Request::METHOD_POST,
            uri: $this->router->generate('sign_in'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $user->getEmail(),
                'password' => self::DEFAULT_PASSWORD,
            ])
        );

        $response = $this->client->getResponse();

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Sign in fail: ' . $response->getContent());
        }

        $data = json_decode($response->getContent(), true);

        if ($token = $data['token'] ?? null) {
            $this->client->setServerParameter('HTTP_AUTHORIZATION', sprintf('Bearer %s', $token));
        }
    }
}
