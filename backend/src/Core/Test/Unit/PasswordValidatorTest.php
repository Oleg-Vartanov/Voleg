<?php

namespace App\Core\Test\Unit;

use App\Core\Validator\Constraints\Password;
use App\Core\Validator\Constraints\PasswordValidator;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<PasswordValidator>
 */
#[TestDox('Password')]
class PasswordValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): PasswordValidator
    {
        return new PasswordValidator();
    }

    #[TestDox('a constraint other than Password is ignored')]
    public function testIgnoresOtherConstraint(): void
    {
        $this->validator->validate('a', new NotNull());
        $this->assertNoViolation();
    }

    #[TestDox('null and empty values are ignored')]
    public function testIgnoresEmptyValues(): void
    {
        $this->validator->validate(null, new Password());
        $this->assertNoViolation();

        $this->validator->validate('', new Password());
        $this->assertNoViolation();
    }

    #[TestDox('a non-string value is rejected')]
    public function testRejectsNonString(): void
    {
        $this->validator->validate(12345678, new Password());

        $this->buildViolation('The value must be a string.')->assertRaised();
    }

    #[TestDox('a password shorter than 8 characters is rejected')]
    public function testTooShort(): void
    {
        $this->validator->validate(str_repeat('a', 7), new Password());

        $this->buildViolation('Password must be at least 8 characters long.')->assertRaised();
    }

    #[TestDox('a password longer than 255 characters is rejected')]
    public function testTooLong(): void
    {
        $this->validator->validate(str_repeat('a', 256), new Password());

        $this->buildViolation('Password must be at most 255 characters long.')->assertRaised();
    }

    #[TestDox('length boundaries are valid')]
    public function testBoundaries(): void
    {
        $constraint = new Password();

        $this->validator->validate(str_repeat('a', 8), $constraint);
        $this->assertNoViolation();

        $this->validator->validate(str_repeat('a', 255), $constraint);
        $this->assertNoViolation();
    }

    #[TestDox('length is counted in characters, not bytes')]
    public function testMultibyteCharacters(): void
    {
        $constraint = new Password();

        $this->validator->validate(str_repeat('é', 8), $constraint);
        $this->assertNoViolation();

        $this->validator->validate(str_repeat('é', 255), $constraint);
        $this->assertNoViolation();
    }

    #[TestDox('a passphrase with spaces and no digits, symbols or capitals is valid')]
    public function testPassphrase(): void
    {
        $this->validator->validate('correct horse battery staple again today', new Password());
        $this->assertNoViolation();
    }

    #[TestDox('a valid password is checked against breached passwords, ignoring API errors')]
    public function testChecksCompromisedPassword(): void
    {
        $this->expectValidateValue(0, 'password', [new NotCompromisedPassword(skipOnError: true)]);

        $this->validator->validate('password', new Password());
    }

    #[TestDox('an invalid-length password skips the breached password check')]
    public function testSkipsCompromisedCheckWhenLengthInvalid(): void
    {
        $this->expectNoValidate();

        $this->validator->validate('short', new Password());

        $this->buildViolation('Password must be at least 8 characters long.')->assertRaised();
    }
}
