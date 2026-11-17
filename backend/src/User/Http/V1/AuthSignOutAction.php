<?php

namespace App\User\Http\V1;

use App\Core\Http\ApiController;
use App\User\Service\RefreshTokenService;
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
            description: 'Signed out',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Signed out.'),
            ])
        ),
    ],
)]
#[Route('/auth/sign-out', name: 'auth_sign_out', methods: [Request::METHOD_POST])]
class AuthSignOutAction extends ApiController
{
    public function __construct(
        private readonly RefreshTokenService $refreshTokens,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->refreshTokens->revokeCurrent($request);
        $response = $this->json(['message' => 'Signed out.']);
        $this->refreshTokens->clearCookie($request, $response);

        return $response;
    }
}
