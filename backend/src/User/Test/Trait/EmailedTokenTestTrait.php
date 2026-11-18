<?php

namespace App\User\Test\Trait;

use RuntimeException;
use Symfony\Component\Mime\Email;

/**
 * Reads the token from the link in the last sent email, so tests go through
 * the same path as a user clicking it.
 */
trait EmailedTokenTestTrait
{
    protected static function lastEmailedToken(): string
    {
        $messages = self::getMailerMessages();
        $email = end($messages);
        if (!$email instanceof Email) {
            throw new RuntimeException('No email was sent.');
        }

        if (preg_match('/[?&]token=([a-f0-9]{64})/', (string) $email->getHtmlBody(), $matches) !== 1) {
            throw new RuntimeException('The last email has no token link.');
        }

        return $matches[1];
    }
}
