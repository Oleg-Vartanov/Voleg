<?php

namespace App\User\Http\V1;

use App\Core\Http\ApiController;
use App\User\Http\V1\Request\VerificationLinkDto;
use App\User\Repository\UserRepository;
use App\User\Service\UserService;
use OpenApi\Attributes as OA;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Get(
    tags: ['Authorization'],
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
class AuthVerifyEmailChangeAction extends ApiController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ParameterBagInterface $parameterBag,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(
        #[MapQueryString] VerificationLinkDto $link,
    ): Response {
        $user = $this->userRepository->find($link->userId)
            ?? throw new NotFoundHttpException();

        $verified = $this->userService->verifyEmailChange(
            $link->code,
            $user,
        );
        $urlParameter = $verified ? 'client.url.email-change-success' : 'client.url.email-change-fail';

        return $this->redirect(
            $this->parameterBag->get($urlParameter),
            Response::HTTP_SEE_OTHER,
        );
    }
}
