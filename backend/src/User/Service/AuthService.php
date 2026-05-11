<?php

namespace App\User\Service;

use App\Core\Service\Mailer;
use App\User\Entity\User;
use App\User\Entity\UserToken;
use App\User\Enum\UserTokenTypeEnum;
use App\User\Http\V1\Request\SignUpDto;
use App\User\Repository\UserRepository;
use App\User\Repository\UserTokenRepository;
use LogicException;
use Random\RandomException;
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
        private UserTokenService $tokenService,
        private UserTokenRepository $tokenRepository,
    ) {
    }

    /** @throws TransportExceptionInterface|RandomException */
    public function signUp(SignUpDto $dto): void
    {
        $user = $this->userService->createByDto($dto);
        $this->userRepository->save($user);

        $this->sendVerificationEmail($user);
    }

    public function verifyUser(UserToken $token, string $secret): bool
    {
        if (
            $token->isExpired()
            || $token->getType() !== UserTokenTypeEnum::VERIFICATION
            || !$this->tokenService->verifySecret($secret, $token->getSecret())
        ) {
            return false;
        }

        $user = $token->getUser();
        $user->setVerified(true);
        $this->userRepository->save($user, true);

        return true;
    }

    /**
     * @throws TransportExceptionInterface|RandomException
     */
    public function sendVerificationEmail(User $user): void
    {
        if ($user->isVerified()) {
            throw new LogicException('User is already verified.');
        }

        [$token, $secret] = $this->tokenService->createToken(
            type: UserTokenTypeEnum::VERIFICATION,
            user: $user,
        );

        $this->tokenRepository->save($token, true);

        $verifyLink = $this->router->generate('sign_up_verify', [
            'selector' => $token->getSelector(),
            'secret' => $secret->plain,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->send(
            template: 'email/signUp.html.twig',
            to: $user->getEmail(),
            subject: 'Verify Sign Up',
            context: [
                'verifyLink' => $verifyLink,
                'displayName' => $user->getDisplayName(),
            ],
        );
    }
}
