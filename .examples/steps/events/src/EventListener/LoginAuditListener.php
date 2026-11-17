<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class)]
final class LoginAuditListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(LoginSuccessEvent $event): void
    {
        $this->logger->info('Вход пользователя', ['user' => $event->getUser()->getUserIdentifier()]);
    }
}
