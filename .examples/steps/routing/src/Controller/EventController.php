<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController
{
    #[Route('/events', name: 'event_index', methods: ['GET'])]
    public function index(): Response
    {
        return new Response('Афиша мероприятий');
    }

    #[Route('/events/{id}', name: 'event_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        return new Response('Мероприятие ' . $id);
    }
}
