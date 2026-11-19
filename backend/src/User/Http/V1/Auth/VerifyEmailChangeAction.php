<?php

namespace App\User\Http\V1\Auth;

use App\Core\Http\ApiController;
use App\Core\Service\AntiEnumerationLimiter;
use App\User\Service\EmailChangeService;
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
            description: 'Email verification results redirected to client',
        ),
        new OA\Response(
            response: Response::HTTP_NOT_FOUND,
            description: 'Invalid link',
        ),
    ],
)]
#[Route('/auth/email-change/verify', name: 'email_change_verify', methods: [Request::METHOD_GET])]
class VerifyEmailChangeAction extends ApiController
{
    public function __construct(
        private readonly EmailChangeService $emailChangeService,
        private readonly ParameterBagInterface $parameterBag,
        private readonly AntiEnumerationLimiter $limiter,
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapQueryParameter] string $token,
    ): Response {
        if ($this->limiter->limit('emailChange' . $request->getClientIp())) {
            return $this->limitResponse();
        }

        $urlParameter = $this->emailChangeService->emailChange($token)
            ? 'client.url.email-change-success'
            : 'client.url.email-change-fail';

        return $this->redirect(
            $this->parameterBag->get($urlParameter),
            Response::HTTP_SEE_OTHER,
        );
    }
}
