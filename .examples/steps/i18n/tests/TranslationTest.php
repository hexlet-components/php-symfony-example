<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Process\Process;

final class TranslationTest extends WebTestCase
{
    public function testLocaleKeepsTheSameEvents(): void
    {
        new Process([PHP_BINARY, 'bin/reset.php', 'test'], dirname(__DIR__), ['APP_ENV' => 'test'])->mustRun();
        $client = self::createClient();
        $client->request('GET', '/events');
        self::assertSelectorTextContains('h1', 'Афиша мероприятий');
        self::assertSelectorExists('html[lang="ru"]');
        $client->request('GET', '/en/events');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Events');
        self::assertSelectorExists('html[lang="en"]');
        self::assertSelectorTextContains('li', 'Встреча PHP-сообщества');
        $client->request('GET', '/fr/events');
        self::assertResponseStatusCodeSame(404);
    }
}
