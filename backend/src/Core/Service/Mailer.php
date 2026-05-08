<?php

namespace App\Core\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

readonly class Mailer
{
    private string $noReply;

    /** @var mixed[] */
    private array $context;

    public function __construct(
        private MailerInterface $mailer,
        private ParameterBagInterface $parameterBag,
    ) {
        $this->noReply = 'no-reply@' . $this->parameterBag->get('app.mail.domain');
        $this->context = [
            'supportEmail' => $this->parameterBag->get('app.support.email'),
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
