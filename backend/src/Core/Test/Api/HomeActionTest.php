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
        $uri = $this->router->generate('home');
        $this->client->request(Request::METHOD_GET, $uri);
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseHeaderSame('Location', '/doc');
    }
}
