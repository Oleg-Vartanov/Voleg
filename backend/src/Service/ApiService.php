<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ApiService
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    /** Used to validate request DTO. */
    public function isValid(mixed $value): bool
    {
        return $this->validator->validate($value)->count() === 0;
    }

    public function validatorErrorResponse(mixed $value, string $message = 'Validation error'): ?JsonResponse
    {
        $errors = $this->validator->validate($value);
        if ($errors->count() > 0) {
            return $this->jsonResponse([
                'message' => $message,
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return null;
    }

    private function jsonResponse(
        mixed $data,
        int $status = Response::HTTP_OK,
        array $headers = [],
        array $context = []
    ): JsonResponse {
        $json = $this->serializer->serialize($data, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));

        return new JsonResponse($json, $status, $headers, true);
    }
}