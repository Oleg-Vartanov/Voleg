<?php

namespace App\Core\Http;

use App\Core\Http\Response\MessageResponse;
use App\Core\ValueObject\Validator\ValidationError;
use App\Core\ValueObject\Validator\Violation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class ApiController extends AbstractController
{
    protected function messageResponse(
        string $message = '',
        int $responseCode = Response::HTTP_OK
    ): JsonResponse {
        return $this->json(new MessageResponse($message), $responseCode);
    }

    /**
     * @param Violation[]|Violation $violations
     */
    protected function validationErrorResponse(array|Violation $violations): JsonResponse
    {
        if ($violations instanceof Violation) {
            $violations = [$violations];
        }

        return $this->json(new ValidationError($violations), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function limitResponse(): JsonResponse
    {
        return $this->messageResponse(
            'Too many attempts. Try again later.',
            Response::HTTP_TOO_MANY_REQUESTS
        );
    }

    protected function notFound(): void
    {
        throw new NotFoundHttpException();
    }

    protected function accessDenied(): void
    {
        throw new AccessDeniedHttpException();
    }
}
