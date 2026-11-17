<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\SummaryFormatterInterface;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(Request $request, EventRepository $events): Response
    {
        $query = $request->query->getString('q');
        $records = $events->findUpcoming($query);
        $summaries = [];
        foreach ($records as $event) {
            $summaries[$event->getId()] = $this->summaryFormatter->format($event->getDescription());
        }
        return $this->render('event/index.html.twig', [
            'events' => $records,
            'summaries' => $summaries,
            'query' => $query,
            'pageTitle' => $this->getParameter('app.title'),
        ]);
    }

    #[Route('/events/{id}', name: 'event_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, EventRepository $events): Response
    {
        $event = $events->find($id);
        if ($event === null) {
            throw $this->createNotFoundException('Мероприятие не найдено');
        }
        return $this->render('event/show.html.twig', ['event' => $event]);
    }

    #[Route('/events/new', name: 'event_new', methods: ['GET'], priority: 10)]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $event = new Event();

        $form = $this->createForm(EventType::class, $event);

        return $this->render('event/form.html.twig', ['form' => $form, 'title' => 'Новое мероприятие']);
    }
}
