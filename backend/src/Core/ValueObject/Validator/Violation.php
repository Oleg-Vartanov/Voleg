<?php

namespace App\Core\ValueObject\Validator;

use OpenApi\Attributes as OA;

#[OA\Schema(title: 'Violation')]
final class Violation
{
    public function __construct(
        #[OA\Property(example: 'password')]
        public string $propertyPath,
        #[OA\Property(example: 'This value is too short. It should have 6 characters or more.')]
        public string $title,
    ) {
    }
}
