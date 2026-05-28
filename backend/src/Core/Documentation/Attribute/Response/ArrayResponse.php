<?php

namespace App\Core\Documentation\Attribute\Response;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ArrayResponse extends Response
{
    public function __construct(
        string $type,
        int $responseCode = 200,
        string $description = 'Success',
        ?array $groups = null,
    ) {
        parent::__construct(
            response: $responseCode,
            description: $description,
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    ref: new Model(type: $type, groups: $groups)
                )
            )
        );
    }
}
