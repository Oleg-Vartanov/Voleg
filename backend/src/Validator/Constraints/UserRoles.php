<?php

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute] class UserRoles extends Constraint
{
    public string $message = 'Access Denied: Requires roles {{ roles }} or other access (e.g., ownership).';

    #[HasNamedArguments]
    public function __construct(
        public array $roles = [],
        public ?string $allowedUserIdentifierGetter = null, // Allow to a user by its UserIdentifier;
        array $options = [],
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