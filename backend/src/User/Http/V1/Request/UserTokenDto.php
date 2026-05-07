<?php

namespace App\User\Http\V1\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'User Token Dto')]
class UserTokenDto
{
    #[Assert\NotBlank]
    public string $selector;

    #[Assert\NotBlank]
    public string $secret;
}
