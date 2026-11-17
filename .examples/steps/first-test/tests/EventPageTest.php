<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventPageTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/events');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Афиша мероприятий');
        self::assertSelectorExists('a[href="/events/1"]');
    }

    public function testEmptySearch(): void
    {
        $client = static::createClient();
        $client->request('GET', '/events?q=zzzzzz');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('li', 'Мероприятия не найдены');
    }
}
