<?php

namespace App\Core\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'home', methods: [Request::METHOD_GET])]
class HomeAction
{
    public function __invoke(): RedirectResponse
    {
        return new RedirectResponse('/doc', Response::HTTP_FOUND);
    }
}
