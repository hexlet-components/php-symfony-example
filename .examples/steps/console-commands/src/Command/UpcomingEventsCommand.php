<?php

namespace App\Command;

use App\Repository\EventRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:events:upcoming', description: 'Show upcoming events')]
final class UpcomingEventsCommand
{
    public function __construct(
        private readonly EventRepository $events,
    ) {}

    public function __invoke(SymfonyStyle $io, #[Argument(description: 'Number of events')] string $limit = '5'): int
    {
        if (!ctype_digit($limit) || (int) $limit < 1 || (int) $limit > 100) {
            $io->error('Количество должно быть целым числом от 1 до 100');
            return Command::INVALID;
        }
        foreach ($this->events->findUpcoming('', (int) $limit) as $event) {
            $io->writeln($event->getStartsAt()->format('d.m.Y H:i') . ' ' . $event->getTitle());
        }
        return Command::SUCCESS;
    }
}
