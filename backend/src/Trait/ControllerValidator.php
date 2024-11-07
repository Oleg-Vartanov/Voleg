<?php

namespace App\Trait;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ControllerValidator
{
    protected ValidatorInterface $validator;

    public function isValid(mixed $value): bool
    {
        return $this->validator->validate($value)->count() === 0;
    }

    public function validationErrorResponse(mixed $value, string|GroupSequence|array|null $groups = null): ?JsonResponse
    {
        $errors = $this->validator->validate($value, groups: $groups);
        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return null;
    }
}