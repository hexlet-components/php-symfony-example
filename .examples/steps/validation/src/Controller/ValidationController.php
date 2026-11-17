<?php

namespace App\Controller;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ValidationController extends AbstractController
{
    #[Route('/_validation', name: 'validation_preview', methods: ['GET'])]
    public function validationPreview(ValidatorInterface $validator): Response
    {
        $event = new Event();
        $event->setDescription('Разбор небольших приложений.');
        $event->setStartsAt(new \DateTimeImmutable('2030-06-01 18:00'));
        $violations = $validator->validate($event);
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
        }
        return new Response(implode("\n", $messages), headers: ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
