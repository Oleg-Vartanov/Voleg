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
            'username' => 'john-doe-' . $index,
            'roles' => [],
        ];
        $userData = array_merge($defaults, $userData);

        if ($isAdmin === true) {
            $userData['roles'][] = RoleEnum::ROLE_ADMIN->value;
        }

        $user = static::getContainer()->get(UserService::class)->create(
            $userData['email'],
            $userData['password'],
            $userData['username'],
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
        $this->client->jsonRequest(
            method: Request::METHOD_POST,
            uri: $this->router->generate('sign_in'),
            parameters: [
                'email' => $user->getEmail(),
                'password' => UserFixture::DEFAULT_PASSWORD,
            ]
        );

        if ($this->getResponseStatusCode() !== 200) {
            throw new RuntimeException('Sign in fail: ' . $this->getResponseContent());
        }

        if ($token = $this->getResponseData()['token'] ?? null) {
            $this->client->setServerParameter('HTTP_AUTHORIZATION', sprintf('Bearer %s', $token));
        }

        return $user;
    }

    public function getResponseStatusCode(): int
    {
        return $this->client->getResponse()->getStatusCode();
    }

    public function getResponseContent(): string
    {
        return $this->client->getResponse()->getContent();
    }

    public function getResponseData(): mixed
    {
        return json_decode($this->getResponseContent(), true);
    }
}
