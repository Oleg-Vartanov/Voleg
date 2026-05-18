<?php

namespace App\User\Http\V1;

use App\Core\Documentation\Attribute\Response\AccessDeniedResponse;
use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Http\ApiController;
use App\User\Http\V1\Trait\UserControllerTrait;
use App\User\Repository\UserContactRepository;
use App\User\Repository\UserRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Delete(
    security: [['Bearer' => []]],
    tags: ['User'],
    responses: [
        new MessageResponse(Response::HTTP_NO_CONTENT, 'Contact removed'),
        new AccessDeniedResponse(),
        new NotFoundResponse('User not found'),
    ],
)]
#[Route('/users/{id}/contacts/{contactId}', name: 'user_contact_delete', methods: [Request::METHOD_DELETE])]
class UserContactDeleteAction extends ApiController
{
    use UserControllerTrait;

    public function __construct(
        private readonly UserContactRepository $contactRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(int $id, int $contactId): Response
    {
        $this->checkModifyAccess($id);

        $contact = $this->contactRepository->findOneByUsers($id, $contactId)
            ?? $this->notFound();

        $this->contactRepository->remove($contact, true);

        return $this->messageResponse('Contact removed', Response::HTTP_NO_CONTENT);
    }
}
