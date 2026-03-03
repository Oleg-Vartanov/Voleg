<?php

namespace App\User\Controller;

use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use LogicException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Post(
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'name@mail.com'),
                new OA\Property(property: 'password', type: 'string', example: 'Password!1'),
            ]
        ),
    ),
    tags: ['Authorization'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Sign in successful',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'token', type: 'string', example: 'jwt-token-string'),
            ])
        ),
        new UnauthorizedResponse('Missing credentials'),
    ],
)]
#[Route('/auth/sign-in', name: 'sign_in', methods: [Request::METHOD_POST])]
class AuthSignInAction
{
    public function __invoke(): void
    {
        throw new LogicException('Route should be intercepted and should not enter here.');
    }
}
