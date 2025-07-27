<?php

namespace App\Core\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute] class EqualParamConfig extends Constraint
{
    public string $message = 'Invalid value.';

    public function __construct(
        public string $name,
        mixed $options = [],
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct($options, $groups, $payload);
    }

    /** @inheritDoc */
    public function getTargets(): array|string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
