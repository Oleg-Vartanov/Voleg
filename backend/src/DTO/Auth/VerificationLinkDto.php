<?php

namespace App\DTO\Auth;

use App\Trait\Arrayable;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Verification Link')]
class VerificationLinkDto
{
    use Arrayable;

    #[Groups(['documentation'])]
    #[OA\Property(type: 'integer')]
    #[Assert\NotBlank, Assert\Type('digit'), Assert\Positive]
    public mixed $userId;

    #[Groups(['documentation'])]
    #[OA\Property(type: 'string')]
    #[Assert\NotBlank, Assert\Type('string')]
    public mixed $code;

    #[Groups(['documentation'])]
    #[OA\Property(type: 'string')]
    #[Assert\Type('string'), Assert\Url]
    public mixed $redirectUrl;
}