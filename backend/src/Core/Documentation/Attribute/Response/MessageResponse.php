<?php

namespace App\Core\Documentation\Attribute\Response;

use App\Core\Http\Response\MessageResponse as MessageResponseDto;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Response;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class MessageResponse extends Response
{
    public function __construct(
        int $responseCode = 200,
        string $description = 'Success',
        ?string $message = null,
    ) {
        parent::__construct(
            response: $responseCode,
            description: $description,
            content: new OA\JsonContent(
                ref: new Model(type: MessageResponseDto::class),
                example: ['message' => $message ?? $description],
            ),
        );
    }
}
