<?php

namespace App\Controller;

use App\DTO\Auth\UserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class AuthController extends AbstractController
{
    #[Route('/sign-in/json', name: 'sign_in_json', methods: ['POST'])]
    public function signIn(#[CurrentUser] ?User $user): Response
    {
        if (is_null($user)) {
            return $this->json(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        // TODO
        $token = 'token'; // somehow create an API token for $user

        return $this->json([
            'user'  => $user->getUserIdentifier(),
            'token' => $token,
        ]);
    }

    #[Route('/sign-up/create', name: 'sign_up', methods: ['POST'])]
    public function signUp(
        Request $request,
        ValidatorInterface $validator,
        AuthService $authService
    ): Response {
        $userParams = $request->getPayload()->all();

        $userDto = UserDto::createByArray($userParams);

        $errors = $validator->validate($userDto);
        if ($errors->count() > 0) {
            return $this->json([
                'message' => 'Sign up error.',
                'errors' => $errors
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $authService->signUp($userDto);

        return $this->json([
            'message' => 'User was created. Now you need to verify it via email.'
        ], Response::HTTP_CREATED);
    }

    #[Route('/sign-up/verify', name: 'sign_up_verify', methods: ['GET'])]
    public function verifyUser(
        Request $request,
        UserRepository $userRepository,
        AuthService $authService,
        ParameterBagInterface $parameterBag,
    ): Response {
        $userId = $request->query->get('userId');
        $code = $request->query->get('code');
        $redirectUrl = $request->query->get('redirectUrl');

        if (is_null($userId) || is_null($code) || is_null($redirectUrl)) {
            return new Response('Required GET params: userId, code, redirectUrl', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $userRepository->find($userId) ?? throw new NotFoundHttpException();

        if ($user->isVerified()) $this->redirect($redirectUrl);

        $verified = $authService->verifyUser($code, $user);

        $template = $verified ? 'email/verification-success.html.twig' : 'email/verification-error.html.twig';

        return $this->render($template, [
            'displayName' => $user->getDisplayName(),
            'supportEmail' => $parameterBag->get('support_email'),
            'continueLink' => $redirectUrl,
        ]);
    }
}