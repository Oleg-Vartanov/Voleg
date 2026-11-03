<?php

namespace App\Core\Test\Unit;

use App\Core\Validator\Constraints\IntRange;
use App\Core\Validator\Constraints\IntRangeValidator;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<IntRangeValidator>
 */
#[TestDox('IntRange')]
class IntRangeValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): IntRangeValidator
    {
        return new IntRangeValidator();
    }

    #[TestDox('a constraint other than IntRange is ignored')]
    public function testIgnoresOtherConstraint(): void
    {
        $this->validator->validate(1, new NotNull());
        $this->assertNoViolation();
    }

    #[TestDox('a non-numeric value is ignored')]
    public function testIgnoresNonNumericValue(): void
    {
        $this->validator->validate('abc', new IntRange(signed: true));
        $this->assertNoViolation();
    }

    #[TestDox('signed: values inside the 32-bit range are valid')]
    public function testSignedValid(): void
    {
        $constraint = new IntRange(signed: true);

        $this->validator->validate(null, $constraint);
        $this->assertNoViolation();

        $this->validator->validate(1, $constraint);
        $this->assertNoViolation();

        $this->validator->validate(-1, $constraint);
        $this->assertNoViolation();

        $this->validator->validate(IntRange::SIGNED_MIN, $constraint);
        $this->assertNoViolation();

        $this->validator->validate(IntRange::SIGNED_MAX, $constraint);
        $this->assertNoViolation();
    }

    #[TestDox('signed: values outside the 32-bit range are rejected')]
    public function testSignedInvalid(): void
    {
        $constraint = new IntRange(signed: true);

        $this->validator->validate(IntRange::SIGNED_MAX + 1, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ min }}', (string) IntRange::SIGNED_MIN)
            ->setParameter('{{ max }}', (string) IntRange::SIGNED_MAX)
            ->assertRaised();
    }

    #[TestDox('signed: values below the 32-bit floor are rejected')]
    public function testSignedInvalidBelowMin(): void
    {
        $constraint = new IntRange(signed: true);
        $value = IntRange::SIGNED_MIN - 1;

        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ min }}', (string) IntRange::SIGNED_MIN)
            ->setParameter('{{ max }}', (string) IntRange::SIGNED_MAX)
            ->assertRaised();
    }

    #[TestDox('unsigned: values inside the 32-bit range are valid')]
    public function testUnsignedValid(): void
    {
        $constraint = new IntRange(signed: false);

        $this->validator->validate(0, $constraint);
        $this->assertNoViolation();

        $this->validator->validate(1, $constraint);
        $this->assertNoViolation();

        $this->validator->validate(IntRange::UNSIGNED_MAX, $constraint);
        $this->assertNoViolation();
    }

    #[TestDox('unsigned: negative values are rejected')]
    public function testUnsignedRejectsNegative(): void
    {
        $constraint = new IntRange(signed: false);

        $this->validator->validate(-1, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ min }}', (string) IntRange::UNSIGNED_MIN)
            ->setParameter('{{ max }}', (string) IntRange::UNSIGNED_MAX)
            ->assertRaised();
    }

    #[TestDox('unsigned: values above the 32-bit ceiling are rejected')]
    public function testUnsignedRejectsAboveMax(): void
    {
        $constraint = new IntRange(signed: false);
        $value = IntRange::UNSIGNED_MAX + 1;

        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ min }}', (string) IntRange::UNSIGNED_MIN)
            ->setParameter('{{ max }}', (string) IntRange::UNSIGNED_MAX)
            ->assertRaised();
    }
}
