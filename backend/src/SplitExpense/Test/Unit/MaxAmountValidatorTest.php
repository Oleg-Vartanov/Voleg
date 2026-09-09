<?php

namespace App\SplitExpense\Test\Unit;

use App\SplitExpense\Validator\Constraints\MaxAmount;
use App\SplitExpense\Validator\Constraints\MaxAmountValidator;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<MaxAmountValidator>
 */
#[TestDox('Split Expense')]
class MaxAmountValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): MaxAmountValidator
    {
        return new MaxAmountValidator();
    }

    #[TestDox('MaxAmount: values up to the INTEGER ceiling are valid')]
    public function testValid(): void
    {
        $constraint = new MaxAmount();

        $this->validator->validate(null, $constraint);
        $this->assertNoViolation();

        $this->validator->validate(1, $constraint);
        $this->assertNoViolation();

        $this->validator->validate(-1, $constraint);
        $this->assertNoViolation();

        $this->validator->validate(-MaxAmount::MAX, $constraint);
        $this->assertNoViolation();

        $this->validator->validate(MaxAmount::MAX, $constraint);
        $this->assertNoViolation();
    }

    #[TestDox('MaxAmount: values above the INTEGER ceiling are rejected')]
    public function testInvalid(): void
    {
        $this->validator->validate(MaxAmount::MAX + 1, new MaxAmount());

        $this->buildViolation((new MaxAmount())->message)
            ->setParameter('{{ value }}', (string) (MaxAmount::MAX + 1))
            ->setParameter('{{ compared_value }}', (string) MaxAmount::MAX)
            ->assertRaised();
    }

    #[TestDox('MaxAmount: values below the INTEGER floor are rejected')]
    public function testInvalidNegative(): void
    {
        $this->validator->validate(-MaxAmount::MAX - 1, new MaxAmount());

        $this->buildViolation((new MaxAmount())->message)
            ->setParameter('{{ value }}', (string) (-MaxAmount::MAX - 1))
            ->setParameter('{{ compared_value }}', (string) MaxAmount::MAX)
            ->assertRaised();
    }
}
