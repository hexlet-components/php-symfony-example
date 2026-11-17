<?php

namespace App\Tests;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EventValidationTest extends KernelTestCase
{
    public function testTheBlankTitleAndValidObject(): void
    {
        self::bootKernel();
        $validator = self::getContainer()->get(ValidatorInterface::class);
        $event = new Event()
            ->setDescription('Разбор небольших приложений.')
            ->setStartsAt(new \DateTimeImmutable('2030-06-01 18:00'));
        $violations = $validator->validate($event);
        self::assertCount(1, $violations);
        self::assertSame('title', $violations[0]->getPropertyPath());
        $event->setTitle('Встреча PHP-сообщества');
        self::assertCount(0, $validator->validate($event));
    }
}
