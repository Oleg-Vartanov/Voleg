<?php

namespace App\Core\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute] class UniqueEntityField extends Constraint
{
    /**
     * @param mixed[] $options
     */
    public function __construct(
        public string $entityClass,
        public string $field = 'id',
        public string $message = 'This value is already used.',
        array $options = [],
        ?array $groups = null,
        mixed $payload = null
    ) {
        $options = array_merge(['entityClass' => $entityClass, 'field' => $field], $options);

        parent::__construct($options, $groups, $payload);
    }

    /** @inheritDoc */
    public function getTargets(): array|string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
