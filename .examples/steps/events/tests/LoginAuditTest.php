<?php

namespace App\Tests;

use Monolog\Handler\TestHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Process\Process;

final class LoginAuditTest extends WebTestCase
{
    public function testRealLoginDispatchesTheSecurityEvent(): void
    {
        new Process([PHP_BINARY, 'bin/reset.php', 'test'], dirname(__DIR__), ['APP_ENV' => 'test'])->mustRun();
        $client = self::createClient();
        $crawler = $client->request('GET', '/login');
        $client->submit($crawler->selectButton('Войти')->form(), [
            '_username' => 'alice@example.test',
            '_password' => 'study-password',
        ]);
        self::assertResponseRedirects('/events');
        $handler = self::getContainer()->get('monolog.handler.login_test');
        self::assertInstanceOf(TestHandler::class, $handler);
        self::assertTrue($handler->hasInfoThatContains('Вход пользователя'));
        $records = $handler->getRecords();
        $record = array_values(array_filter(
            $records,
            fn($record): bool => $record->message === 'Вход пользователя',
        ))[0];
        self::assertSame('alice@example.test', $record->context['user']);
    }
}
