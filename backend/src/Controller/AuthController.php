<?php

namespace App\Controller;

use App\DTO\Auth\UserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use http\Exception\InvalidArgumentException;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;
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

    #[Route('/sign-in/check-link', name: 'sign_in_check_link')]
    public function signInCheckLink(): never
    {
        throw new LogicException('Authenticator should intercept the requests.');
    }

    #[Route('/sign-in/request-link', name: 'sign_in_request_link', methods: ['POST'])]
    public function signInRequestLink(
        Request $request,
        NotifierInterface $notifier,
        LoginLinkHandlerInterface $loginLinkHandler,
        UserRepository $userRepository
    ): Response {
        $email = $request->getPayload()->get('email');

        $user = $userRepository->findOneByEmail($email) ?? throw new InvalidArgumentException('Invalid email');

        $loginLinkDetails = $loginLinkHandler->createLoginLink($user);
        $notification = new LoginLinkNotification($loginLinkDetails, 'Sign In');
        $recipient = new Recipient($user->getEmail());

        $notifier->send($notification, $recipient);

        return $this->json(['message' => 'Notification was send.']);
    }

    #[Route('/sign-up', name: 'sign_up', methods: ['POST'])]
    public function signUp(
        Request $request,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        AuthService $authService
    ): Response {
        $userParams = $request->getPayload()->all();

        $userDto = UserDto::createByArray($userParams);

        $errors = $validator->validate($userDto);
        if (count($errors)) {
            return $this->json([
                'message' => 'Sign up error.',
                'errors' => $errors
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $authService->createUser($userDto);
        $userRepository->save($user);

        // TODO: Verify via email. Send notification to email.

        return $this->json([
            'message' => 'User was created. Now you need to verify it via email.'
        ], Response::HTTP_CREATED);
    }
}