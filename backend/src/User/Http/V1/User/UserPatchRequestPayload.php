<?php

namespace App\User\Http\V1\User;

use OpenApi\Attributes as OA;

#[OA\Schema(title: 'User Update')]
class UserPatchRequestPayload extends UserRequestPayload
{
}
