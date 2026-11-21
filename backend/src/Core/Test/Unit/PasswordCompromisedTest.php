<?php

namespace App\Core\Test\Unit;

use App\Core\Validator\Constraints\Password;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\NotCompromisedPasswordValidator;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[TestDox('Password breached password check')]
class PasswordCompromisedTest extends TestCase
{
    private const string LEAKED_PASSWORD = 'password';
    private const string CLEAN_PASSWORD = 'correct horse battery staple';

    #[TestDox('a leaked password is rejected')]
    public function testLeakedPasswordRejected(): void
    {
        $validator = $this->createValidator(function (string $method, string $url): MockResponse {
            return new MockResponse($this->rangeResponse(self::LEAKED_PASSWORD, 3861493));
        });

        $violations = $validator->validate(self::LEAKED_PASSWORD, new Password());

        self::assertCount(1, $violations);
        self::assertSame(NotCompromisedPassword::COMPROMISED_PASSWORD_ERROR, $violations[0]->getCode());
    }

    #[TestDox('a password absent from the leak list is accepted')]
    public function testCleanPasswordAccepted(): void
    {
        $validator = $this->createValidator(function (string $method, string $url): MockResponse {
            return new MockResponse($this->rangeResponse(self::LEAKED_PASSWORD, 1));
        });

        self::assertCount(0, $validator->validate(self::CLEAN_PASSWORD, new Password()));
    }

    #[TestDox('only the first 5 characters of the SHA-1 hash are sent to the API')]
    public function testOnlyHashPrefixIsSent(): void
    {
        $requestedUrl = null;
        $validator = $this->createValidator(function (string $method, string $url) use (&$requestedUrl): MockResponse {
            $requestedUrl = $url;

            return new MockResponse('');
        });

        $validator->validate(self::CLEAN_PASSWORD, new Password());

        $prefix = substr(strtoupper(sha1(self::CLEAN_PASSWORD)), 0, 5);
        self::assertStringEndsWith('/range/' . $prefix, (string) $requestedUrl);
    }

    #[TestDox('API errors are ignored and the password is accepted')]
    public function testApiErrorIsSkipped(): void
    {
        $validator = $this->createValidator(function (): MockResponse {
            return new MockResponse('', ['http_code' => 503]);
        });

        self::assertCount(0, $validator->validate(self::LEAKED_PASSWORD, new Password()));
    }

    #[TestDox('the API is not called for a password with invalid length')]
    public function testApiNotCalledForInvalidLength(): void
    {
        $calls = 0;
        $validator = $this->createValidator(function () use (&$calls): MockResponse {
            ++$calls;

            return new MockResponse('');
        });

        self::assertCount(1, $validator->validate('short', new Password()));
        self::assertSame(0, $calls);
    }

    private function createValidator(callable $responseFactory): ValidatorInterface
    {
        $client = new MockHttpClient($responseFactory);

        return Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new ConstraintValidatorFactory([
                NotCompromisedPasswordValidator::class => new NotCompromisedPasswordValidator($client),
            ]))
            ->getValidator();
    }

    private function rangeResponse(string $password, int $count): string
    {
        $suffix = substr(strtoupper(sha1($password)), 5);

        return "0018A45C4D1DEF81644B54AB7F969B88D65:1\r\n{$suffix}:{$count}\r\n";
    }
}
