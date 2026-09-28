<?php

namespace App\Core\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

readonly class Mailer
{
    private Address $noReply;

    /** @var mixed[] */
    private array $context;

    public function __construct(
        private MailerInterface $mailer,
        #[Autowire(env: 'APP_MAIL_DOMAIN')]
        private string $domain,
        #[Autowire(env: 'APP_MAIL_NAME')]
        private string $senderName,
        #[Autowire(env: 'APP_SUPPORT_EMAIL')]
        private string $supportEmail,
    ) {
        $this->noReply = new Address(
            address: 'no-reply@' . $this->domain,
            name: $this->senderName,
        );

        $this->context = [
            'supportEmail' => $this->supportEmail,
        ];
    }

    /**
     * @param mixed[] $context
     * @throws TransportExceptionInterface
     */
    public function send(
        string $template,
        string $to,
        string $subject,
        array $context = [],
        ?string $from = null,
    ): void {
        $email = new TemplatedEmail()
            ->from($from ?? $this->noReply)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context(array_merge($this->context, $context))
        ;

        $this->mailer->send($email);
    }
}
