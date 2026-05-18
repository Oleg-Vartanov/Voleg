<?php

namespace App\User\Http\V1;

use App\Core\Documentation\Attribute\Response\AccessDeniedResponse;
use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Http\ApiController;
use App\User\Http\V1\Trait\UserControllerTrait;
use App\User\Repository\UserContactRepository;
use App\User\Repository\UserRepository;
use App\User\Service\UserContactService;
use LogicException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Post(
    security: [['Bearer' => []]],
    tags: ['User'],
    responses: [
        new MessageResponse(Response::HTTP_OK, 'Contact added'),
        new MessageResponse(Response::HTTP_BAD_REQUEST, 'Invalid data'),
        new AccessDeniedResponse(),
        new NotFoundResponse('User not found'),
    ],
)]
#[Route('/users/{id}/contacts/{contactId}', name: 'user_contact_post', methods: [Request::METHOD_POST])]
class UserContactPostAction extends ApiController
{
    use UserControllerTrait;

    public function __construct(
        private readonly UserContactService $service,
        private readonly UserContactRepository $userContactRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(int $id, int $contactId): Response
    {
        $this->checkModifyAccess($id);

        $user = $this->userRepository->findById($id) ?? $this->notFound();
        $contact = $this->userRepository->findById($contactId)
            ?? $this->notFound();

        try {
            $userContact = $this->service->create($user, $contact);
        } catch (LogicException $e) {
            return $this->messageResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->userContactRepository->save($userContact, true);

        return $this->messageResponse('Contact added', Response::HTTP_CREATED);
    }
}
