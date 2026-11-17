<?php

namespace App\User\Http\V1;

use App\Core\Http\ApiController;
use App\User\Exception\RefreshTokenRejected;
use App\User\Service\RefreshTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Post(
    tags: ['User: Auth'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Refresh successful',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'token', type: 'string'),
            ])
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'Refresh token missing, expired, or already used',
        ),
    ],
)]
#[Route('/auth/refresh', name: 'auth_refresh', methods: [Request::METHOD_POST])]
class AuthRefreshAction extends ApiController
{
    public function __construct(
        private readonly RefreshTokenService $refreshTokens,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $response = new JsonResponse();

        try {
            $user = $this->refreshTokens->rotate($request, $response);
        } catch (RefreshTokenRejected) {
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            $response->setData(['message' => 'Invalid refresh token.']);
            $this->refreshTokens->clearCookie($request, $response);

            return $response;
        }

        $response->setData(['token' => $this->jwtManager->create($user)]);

        return $response;
    }
}
