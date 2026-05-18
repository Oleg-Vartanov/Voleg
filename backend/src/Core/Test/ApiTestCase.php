<?php

namespace App\Core\Test;

use App\Core\Test\Trait\ContainerTestTrait;
use App\User\DataFixture\UserFixture;
use App\User\Entity\User;
use App\User\Enum\RoleEnum;
use App\User\Repository\UserRepository;
use App\User\Service\UserService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

abstract class ApiTestCase extends WebTestCase
{
    use ContainerTestTrait;

    protected KernelBrowser $client;
    protected RouterInterface $router;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->router = $this->getService(RouterInterface::class);
    }

    protected function createUser(
        array $userData = [],
        bool $verified = true,
        bool $isAdmin = false,
        bool $flush = true,
    ): User {
        $index = bin2hex(random_bytes(6));
        $defaults = [
            'email' => 'user' . $index . '@example.com',
            'password' => UserFixture::DEFAULT_PASSWORD,
            'displayName' => 'John Doe ' . $index,
            'tag' => 'john-doe-' . $index,
            'roles' => [],
        ];
        $userData = array_merge($defaults, $userData);

        if ($isAdmin === true) {
            $userData['roles'][] = RoleEnum::ROLE_ADMIN->value;
        }

        $user = static::getContainer()->get(UserService::class)->create(
            $userData['email'],
            $userData['password'],
            $userData['displayName'],
            $userData['tag'],
            $userData['roles'],
        );

        if ($verified) {
            $user->setVerified(true);
        }

        $this->getService(UserRepository::class)->save($user, $flush);

        return $user;
    }

    protected function signIn(User $user): User
    {
        $this->client->request(
            method: Request::METHOD_POST,
            uri: $this->router->generate('sign_in'),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $user->getEmail(),
                'password' => UserFixture::DEFAULT_PASSWORD,
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

        return $user;
    }
}
