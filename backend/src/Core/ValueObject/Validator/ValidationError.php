<?php

namespace App\Core\ValueObject\Validator;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

/**
 * Used for documentation.
 */
#[OA\Schema(title: 'Validation Error Response')]
final class ValidationError
{
    public string $title = 'Validation Failed';
    public int $status = 422;

    /**
     * @var Violation[]
     */
    #[OA\Property(type: 'array', items: new OA\Items(ref: new Model(type: Violation::class)))]
    public array $violations;

    /**
     * @param Violation[] $violations
     */
    public function __construct(array $violations)
    {
        $this->violations = $violations;
    }
}
