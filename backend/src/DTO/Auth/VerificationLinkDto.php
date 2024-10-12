<?php

namespace App\DTO\Auth;

use App\Trait\Arrayable;
use Symfony\Component\Validator\Constraints as Assert;

class VerificationLinkDto
{
    use Arrayable;

    #[Assert\NotBlank, Assert\Type('digit'), Assert\Positive]
    public mixed $userId;

    #[Assert\NotBlank, Assert\Type('string')]
    public mixed $code;

    #[Assert\Type('string'), Assert\Url]
    public mixed $redirectUrl;
}