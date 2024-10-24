<?php

namespace App\Controller;

use App\DTO\Auth\SignUpDto;
use App\DTO\Auth\VerificationLinkDto;
use App\DTO\Validator\ValidationErrorResponse;
use App\Entity\User;
use App\Factory\ApiTokenFactory;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Random\RandomException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Tag(name: 'Authorization')]
class AuthController extends ApiController
{
    /* OpenAi Documentation */
    #[OA\RequestBody(
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'email', type: 'string'),
            new OA\Property(property: 'password', type: 'string'),
        ])
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Sign in successful',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'email', type: 'string'),
            new OA\Property(property: 'token', type: 'string'),
        ])
    )]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Missing credentials')]

    /** @throws RandomException */
    #[Route('/auth/sign-in', name: 'sign_in', methods: ['POST'])]
    public function signIn(
        #[CurrentUser] ?User $user,
        ApiTokenFactory $factory,
        EntityManagerInterface $entityManager
    ): Response {
        if (is_null($user)) {
            return $this->json(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $factory->create($user);
        $entityManager->persist($token);
        $entityManager->flush();

        return $this->json([
            'user'  => $user->getUserIdentifier(),
            'token' => $token->getValue(),
        ]);
    }

    /* OpenAi Documentation */
    #[OA\RequestBody(content: new Model(type: SignUpDto::class, groups: ['documentation']))]
    #[OA\Response(response: Response::HTTP_CREATED, description: 'Sign up successful')]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation errors',
        content: new Model(type: ValidationErrorResponse::class)
    )]

    /** @throws TransportExceptionInterface */
    #[Route('/auth/sign-up', name: 'sign_up', methods: ['POST'], format: 'json')]
    public function signUp(Request $request, AuthService $authService): Response
    {
        $dto = SignUpDto::createByArray($request->getPayload()->all());

        if ($response = $this->validationErrorResponse($dto)) {
            return $response;
        }

        $authService->signUp($dto);

        return $this->json([
            'message' => 'User was created. Now you need to verify it via email.'
        ], Response::HTTP_CREATED);
    }

    /* OpenAi Documentation */
    #[OA\RequestBody(content: new Model(type: VerificationLinkDto::class, groups: ['documentation']))]
    #[OA\Response(response: Response::HTTP_OK, description: 'Verified page')]
    #[OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'Invalid link')]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Invalid user')]

    #[Route('/auth/sign-up/verify', name: 'sign_up_verify', methods: ['GET'])]
    public function verifyUser(
        Request $request,
        AuthService $authService,
        ParameterBagInterface $parameterBag,
        UserRepository $userRepository,
    ): Response {
        $link = VerificationLinkDto::createByArray($request->query->all());

        if (!$this->isValid($link)) {
            return new Response('Invalid verification link. Please contact the support.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $userRepository->find($link->userId) ?? throw new NotFoundHttpException();

        $verified = $user->isVerified() || $authService->verifyUser($link->code, $user);
        $template = $verified ? 'email/verification-success.html.twig' : 'email/verification-fail.html.twig';

        return $this->render($template, [
            'displayName' => $user->getDisplayName(),
            'supportEmail' => $parameterBag->get('support_email'),
            'continueLink' => $link->redirectUrl ?? null,
        ]);
    }

    /* OpenAi Documentation */
    #[OA\Response(response: Response::HTTP_NO_CONTENT, description: 'Successfully signed out')]
    #[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized')]

    #[Route('/sign-out', name: 'sign_out', methods: ['POST'])]
    public function signOut(Request $request, Security $security, AuthService $service, EntityManagerInterface $entityManager): Response
    {
        $apiToken = $service->getApiToken($request);
        if (is_null($apiToken)) {
            throw new \LogicException();
        }

        $entityManager->remove($apiToken);
        $entityManager->flush();

        $security->logout(false);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}