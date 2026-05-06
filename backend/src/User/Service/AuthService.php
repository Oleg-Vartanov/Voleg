<?php

namespace App\User\Service;

use App\Core\Service\Mailer;
use App\User\Entity\User;
use App\User\Http\V1\Request\SignUpDto;
use App\User\Repository\UserRepository;
use LogicException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

readonly class AuthService
{
    public function __construct(
        private Mailer $mailer,
        private RouterInterface $router,
        private UserService $userService,
        private UserRepository $userRepository,
    ) {
    }

    /** @throws TransportExceptionInterface */
    public function signUp(SignUpDto $dto): void
    {
        $user = $this->userService->createByDto($dto);
        $this->userRepository->save($user, true);

        $this->sendVerificationEmail($user);
    }

    public function verifyUser(string $code, User $user): bool
    {
        // TODO: hash user code
        $verified = !$user->verificationCodeExpired() && $user->getVerificationCode() === $code;

        if ($verified) {
            $user->setVerified(true);
            $this->userRepository->save($user, true);
        }

        return $verified;
    }

    /** @throws TransportExceptionInterface */
    public function sendVerificationEmail(User $user): void
    {
        if ($user->isVerified()) {
            throw new LogicException('User is already verified.');
        }

        $this->mailer->send(
            template: 'email/sign-up.html.twig',
            to: $user->getEmail(),
            subject: 'Verify Sign Up',
            context: [
                'verifyLink' => $this->createVerificationLink($user),
                'displayName' => $user->getDisplayName(),
            ],
        );
    }

    private function createVerificationLink(User $user): string
    {
        return $this->router->generate('sign_up_verify', [
            'userId' => $user->getId(),
            'code' => $user->getVerificationCode(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
