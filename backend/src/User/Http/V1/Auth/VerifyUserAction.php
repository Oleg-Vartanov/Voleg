<?php

namespace App\User\Http\V1\Auth;

use App\Core\Http\ApiController;
use App\Core\Service\AntiEnumerationLimiter;
use App\User\Service\AuthService;
use OpenApi\Attributes as OA;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Get(
    tags: ['User: Auth'],
    responses: [
        new OA\Response(
            response: Response::HTTP_SEE_OTHER,
            description: 'Verification results redirected to client'
        ),
        new OA\Response(
            response: Response::HTTP_NOT_FOUND,
            description: 'Invalid link'
        ),
    ],
)]
#[Route('/auth/sign-up/verify', name: 'sign_up_verify', methods: [Request::METHOD_GET])]
class VerifyUserAction extends ApiController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly ParameterBagInterface $parameterBag,
        private readonly AntiEnumerationLimiter $limiter,
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapQueryParameter] string $token,
    ): Response {
        if ($this->limiter->limit('verifyUser' . $request->getClientIp())) {
            return $this->limitResponse();
        }

        $urlParameter = $this->authService->verifyUser($token)
            ? 'client.url.auth-verification-success'
            : 'client.url.auth-verification-fail';

        return $this->redirect(
            $this->parameterBag->get($urlParameter),
            Response::HTTP_SEE_OTHER,
        );
    }
}
