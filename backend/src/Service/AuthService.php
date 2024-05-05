<?php

namespace App\Service;

use App\DTO\Auth\UserDto;
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
        private MailerInterface $mailer,
        private UserFactory $userFactory,
        private ParameterBagInterface $parameterBag,
        private RouterInterface $router,
        private EntityManagerInterface $entityManager
    ) {
    }

    /** @throws TransportExceptionInterface */
    public function signUp(UserDto $userDto): void
    {
        $user = $this->userFactory->create($userDto);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->sendVerificationEmail($user);
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

    private function createVerificationLink(User $user): string
    {
        return $this->router->generate('sign_up_verify', [
            'userId' => $user->getId(),
            'code' => $user->getVerificationCode(),
            'redirectUrl' => $this->router->generate('home'),
        ], UrlGeneratorInterface::NETWORK_PATH);
    }

    /** @throws TransportExceptionInterface */
    private function sendVerificationEmail(User $user): void
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
                'verifyLink' => $this->createVerificationLink($user),
                'displayName' => $user->getDisplayName(),
                'supportEmail' => $this->parameterBag->get('support_email'),
            ])
        ;

        $this->mailer->send($email);
    }
}