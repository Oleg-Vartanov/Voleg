<?php

namespace App\Core\Test\Api;

use App\Core\Test\ApiTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('Core')]
class HomeActionTest extends ApiTestCase
{
    #[TestDox('Home action')]
    public function testHomeAction(): void
    {
        $url = $this->router->generate('home');
        $this->client->request(Request::METHOD_GET, $url);
        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertSame('/doc', $response->headers->get('Location'));
    }
}