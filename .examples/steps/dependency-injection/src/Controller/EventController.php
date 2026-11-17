<?php

namespace App\Controller;

use App\Service\SummaryFormatterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    public function __construct(
        private readonly SummaryFormatterInterface $summaryFormatter,
    ) {}

    #[Route('/events', name: 'event_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $query = $request->query->getString('q');
        $events = [
            [
                'id' => 1,
                'title' => 'Встреча PHP-сообщества',
                'description' => 'Обсуждаем тестирование и разработку приложений.',
                'startsAt' => '2030-05-20 19:00:00',
            ],
            [
                'id' => 2,
                'title' => 'Вечер настольных игр',
                'description' => 'Знакомимся с новыми играми.',
                'startsAt' => '2030-05-21 18:00:00',
            ],
        ];
        $events = array_filter(
            $events,
            fn(array $event): bool => $query === '' || mb_stripos($event['title'], $query) !== false,
        );
        foreach ($events as $index => $event) {
            $events[$index]['summary'] = $this->summaryFormatter->format($event['description']);
        }
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'query' => $query,
            'pageTitle' => $this->getParameter('app.title'),
        ]);
    }

    #[Route('/events/{id}', name: 'event_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        return new Response('Мероприятие ' . $id);
    }
}
