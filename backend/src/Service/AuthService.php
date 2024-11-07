<?php

namespace App\Service;

use App\DTO\User\SignUpDto;
use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

readonly class AuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private ParameterBagInterface $parameterBag,
        private RouterInterface $router,
        private UserFactory $userFactory,
    ) {
    }

    /** @throws TransportExceptionInterface */
    public function signUp(SignUpDto $dto): void
    {
        $user = $this->userFactory->create($dto);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->sendVerificationEmail($user, $dto->verificationEmailRedirectUrl ?? null);
    }

    public function verifyUser(string $code, User $user): bool
    {
        $verified = !$user->verificationCodeExpired() && $user->getVerificationCode() === $code;

        if ($verified) {
            $user->setVerified(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $verified;
    }

    public function createVerificationLink(User $user, ?string $redirectUrl = null): string
    {
        return $this->router->generate('sign_up_verify', [
            'userId' => $user->getId(),
            'code' => $user->getVerificationCode(),
            'redirectUrl' => $redirectUrl,
        ], UrlGeneratorInterface::NETWORK_PATH);
    }

    /** @throws TransportExceptionInterface */
    public function sendVerificationEmail(User $user, ?string $redirectUrl = null): void
    {
        if ($user->isVerified()) {
            throw new LogicException('User is already verified.');
        }

        $email = (new TemplatedEmail())
            ->from('no-reply@project.com')
            ->to(new Address($user->getEmail()))
            ->subject('Verify Sign Up')
            ->htmlTemplate('email/sign-up.html.twig')
            ->context([
                'verifyLink' => $this->createVerificationLink($user, $redirectUrl),
                'displayName' => $user->getDisplayName(),
                'supportEmail' => $this->parameterBag->get('support_email'),
            ])
        ;

        $this->mailer->send($email);
    }
}