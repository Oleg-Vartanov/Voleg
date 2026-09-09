<?php

namespace App\SplitExpense\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class MaxAmount extends Constraint
{
    /** Matches the range of the {@see \Doctrine\DBAL\Types\Types::INTEGER} `amount` column. */
    public const int MAX = 2147483647;

    public string $message =
        'The absolute value of {{ value }} should be less than or equal to {{ compared_value }}.';
}
