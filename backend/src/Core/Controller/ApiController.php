<?php

namespace App\Core\Controller;

use App\Core\DTO\Response\MessageResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends AbstractController
{
    protected function messageResponse(
        ?string $message = null,
        int $responseCode = Response::HTTP_OK
    ): JsonResponse {
        return $this->json(new MessageResponse($message ?? ''), $responseCode);
    }
}
