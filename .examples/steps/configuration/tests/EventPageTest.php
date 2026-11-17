<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventPageTest extends WebTestCase
{
    public function testIndexAndEmptyFilter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/events');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Тестовая афиша');
        self::assertSelectorExists('a[href="/events/1"]');
        $client->request('GET', '/events?q=zzzzzz');
        self::assertSelectorTextContains('li', 'Мероприятия не найдены');
        $client->request('GET', '/not-found');
        self::assertResponseStatusCodeSame(404);
    }
}
