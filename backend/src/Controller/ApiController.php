<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    public function __construct(protected ValidatorInterface $validator)
    {
    }

    public function isValid(mixed $value): bool
    {
        return $this->validator->validate($value)->count() === 0;
    }

    public function validationErrorResponse(mixed $value, string $message = 'Validation error'): ?JsonResponse
    {
        $errors = $this->validator->validate($value);
        if ($errors->count() > 0) {
            return $this->json([
                'message' => $message,
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return null;
    }
}