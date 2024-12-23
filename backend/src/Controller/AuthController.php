<?php

namespace App\Controller;

use App\DTO\Documentation\Validator\ValidationErrorResponse;
use App\DTO\User\SignUpDto;
use App\DTO\User\UserDto;
use App\DTO\User\VerificationLinkDto;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Authorization')]
#[Route('/auth')]
class AuthController extends AbstractController
{
    /* OpenAi Documentation */
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'name@mail.com'),
                new OA\Property(property: 'password', type: 'string', example: 'Password!1'),
            ]
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Sign in successful',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'token', type: 'string', example: 'jwt-token-string'),
        ])
    )]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Missing credentials')]

    #[Route('/sign-in', name: 'sign_in', methods: ['POST'])]
    public function signIn() {
        throw new \LogicException('Route should be intercepted and should not enter here.');
    }

    /* OpenAi Documentation */
    #[OA\Response(response: Response::HTTP_CREATED, description: 'Sign up successful')]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation errors',
        content: new Model(type: ValidationErrorResponse::class)
    )]

    /** @throws TransportExceptionInterface */
    #[Route('/sign-up', name: 'sign_up', methods: ['POST'], format: 'json')]
    public function signUp(
        #[MapRequestPayload(validationGroups: [UserDto::SIGN_UP])] SignUpDto $dto,
        AuthService $authService,
    ): Response {
        $authService->signUp($dto);

        return $this->json([
            'message' => 'User was created. Now you need to verify it via email.'
        ], Response::HTTP_CREATED);
    }

    /* OpenAi Documentation */
    #[OA\Response(response: Response::HTTP_OK, description: 'Verified page')]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Invalid link')]

    #[Route('/sign-up/verify', name: 'sign_up_verify', methods: ['GET'])]
    public function verifyUser(
        #[MapQueryString] VerificationLinkDto $link,
        AuthService $authService,
        ParameterBagInterface $parameterBag,
        UserRepository $userRepository,
    ): Response {
        $user = $userRepository->find($link->userId) ?? throw new NotFoundHttpException();

        $verified = $user->isVerified() || $authService->verifyUser($link->code, $user);
        $template = $verified ? 'email/verification-success.html.twig' : 'email/verification-fail.html.twig';

        return $this->render($template, [
            'displayName' => $user->getDisplayName(),
            'supportEmail' => $parameterBag->get('app.support.email'),
            'continueLink' => $link->redirectUrl,
        ]);
    }
}