<?php

namespace App\User\Service;

use App\Core\Service\Mailer;
use App\User\Entity\User;
use App\User\Entity\UserToken;
use App\User\Enum\UserTokenTypeEnum;
use App\User\Repository\UserRepository;
use App\User\Repository\UserTokenRepository;
use LogicException;
use Random\RandomException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

readonly class EmailChangeService
{
    public function __construct(
        private Mailer $mailer,
        private RouterInterface $router,
        private UserTokenService $tokenService,
        private UserTokenRepository $tokenRepository,
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @throws TransportExceptionInterface|RandomException
     */
    public function requestEmailChange(User $user, string $emailChange): void
    {
        if ($user->getEmail() === $emailChange) {
            throw new LogicException('New email is the same as the current one.');
        }

        // Remove older tokens.
        $this->tokenRepository->removeByUser($user, UserTokenTypeEnum::EMAIL_CHANGE);

        [$token, $secret] = $this->tokenService->createToken(
            type: UserTokenTypeEnum::EMAIL_CHANGE,
            user: $user,
            payload: ['emailChange' => $emailChange],
        );

        $this->tokenRepository->save($token, true);

        $verifyLink = $this->router->generate('email_change_verify', [
            'selector' => $token->getSelector(),
            'secret' => $secret->plain,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->send(
            template: 'email/emailChange.html.twig',
            to: $emailChange,
            subject: 'Verify Email Change',
            context: [
                'verifyLink' => $verifyLink,
                'displayName' => $user->getDisplayName(),
            ],
        );
    }

    public function emailChange(UserToken $token, string $secret): bool
    {
        if (
            $token->isExpired()
            || $token->getType() !== UserTokenTypeEnum::EMAIL_CHANGE
            || !$this->tokenService->verifySecret($secret, $token->getSecret())
        ) {
            return false;
        }

        $user = $token->getUser();
        $user->setEmail($token->getEmailChange());
        $this->tokenRepository->remove($token);
        $this->userRepository->save($user, true);

        return true;
    }
}
