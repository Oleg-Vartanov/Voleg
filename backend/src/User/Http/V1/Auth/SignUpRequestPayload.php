<?php

namespace App\User\Http\V1\Auth;

use App\User\Http\V1\User\UserRequestPayload;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'User Sign Up')]
class SignUpRequestPayload extends UserRequestPayload
{
}
