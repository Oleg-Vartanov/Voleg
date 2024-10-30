<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return new Response('Server is running...');
    }

    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        return $this->json(['message' => 'ok']);
    }
}