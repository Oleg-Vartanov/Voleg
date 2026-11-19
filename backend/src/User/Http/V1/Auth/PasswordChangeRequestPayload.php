<?php

namespace App\User\Http\V1\Auth;

use App\Core\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'Password Change Request Payload')]
class PasswordChangeRequestPayload
{
    #[Assert\NotBlank]
    public string $currentPassword;

    #[Assert\NotBlank, CustomAssert\StrongPassword]
    public string $newPassword;
}
