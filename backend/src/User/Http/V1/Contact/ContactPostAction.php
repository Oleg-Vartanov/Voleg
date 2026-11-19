<?php

namespace App\User\Http\V1\Contact;

use App\Core\Documentation\Attribute\Response\AccessDeniedResponse;
use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Http\ApiController;
use App\User\Http\V1\UserControllerTrait;
use App\User\Repository\ContactRepository;
use App\User\Repository\UserRepository;
use App\User\Service\ContactService;
use LogicException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Post(
    security: [['Bearer' => []]],
    tags: ['User: Contacts'],
    responses: [
        new MessageResponse(Response::HTTP_OK, 'Contact added'),
        new MessageResponse(Response::HTTP_BAD_REQUEST, 'Invalid data'),
        new AccessDeniedResponse(),
        new NotFoundResponse('User not found'),
    ],
)]
#[Route('/users/{id}/contacts/{contactId}', name: 'contact_post', methods: [Request::METHOD_POST])]
class ContactPostAction extends ApiController
{
    use UserControllerTrait;

    public function __construct(
        private readonly ContactService $service,
        private readonly ContactRepository $contactRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(int $id, int $contactId): Response
    {
        $this->checkModifyAccess($id);

        $user = $this->userRepository->findById($id) ?? $this->notFound();
        $contactUser = $this->userRepository->findById($contactId)
            ?? $this->notFound();

        try {
            $contact = $this->service->create($user, $contactUser);
        } catch (LogicException $e) {
            return $this->messageResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->contactRepository->save($contact, true);

        return $this->messageResponse('Contact added', Response::HTTP_CREATED);
    }
}
