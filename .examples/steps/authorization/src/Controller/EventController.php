<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Security\EventVoter;
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

    #[Route('/events/new', name: 'event_new', methods: ['GET', 'POST'], priority: 10)]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $event = new Event();
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        $event->setOwner($user);
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($event);
            $manager->flush();
            $this->addFlash('success', 'Мероприятие сохранено');
            return $this->redirectToRoute('event_show', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('event/form.html.twig', ['form' => $form, 'title' => 'Новое мероприятие']);
    }

    #[Route('/events/{id}/edit', name: 'event_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EventRepository $events, EntityManagerInterface $manager): Response
    {
        $event = $events->find($id);
        if ($event === null) {
            throw $this->createNotFoundException('Мероприятие не найдено');
        }
        $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash('success', 'Мероприятие сохранено');
            return $this->redirectToRoute('event_show', ['id' => $id], Response::HTTP_SEE_OTHER);
        }
        return $this->render('event/form.html.twig', ['form' => $form, 'title' => 'Редактирование мероприятия']);
    }

    #[Route('/events/{id}/delete', name: 'event_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        EventRepository $events,
        EntityManagerInterface $manager,
    ): Response {
        $event = $events->find($id);
        if ($event === null) {
            throw $this->createNotFoundException('Мероприятие не найдено');
        }
        $this->denyAccessUnlessGranted(EventVoter::DELETE, $event);
        if (!$this->isCsrfTokenValid('delete-event-' . $id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Неверный CSRF-токен');
        }
        $manager->remove($event);
        $manager->flush();
        return $this->redirectToRoute('event_index', [], Response::HTTP_SEE_OTHER);
    }
}
