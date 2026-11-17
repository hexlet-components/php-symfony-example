<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Process\Process;

final class LocaleAccessTest extends WebTestCase
{
    public function testEnglishWriteUrlsRequireAuthentication(): void
    {
        new Process([PHP_BINARY, 'bin/reset.php', 'test'], dirname(__DIR__), ['APP_ENV' => 'test'])->mustRun();
        $client = self::createClient();
        foreach ([
            ['GET',  '/en/events/new'],
            ['GET',  '/en/events/1/edit'],
            ['POST', '/en/events/1/delete'],
        ] as [$method, $url]) {
            $client->request($method, $url);
            self::assertResponseRedirects('http://localhost/login');
        }
        $user = self::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['email' => 'alice@example.test']);
        $client->loginUser($user);
        $client->request('GET', '/en/events/new');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('label[for="event_title"]', 'Title');
        self::assertSelectorTextContains('button[name="save"]', 'Save');
        $client->request('GET', '/en/events');
        self::assertSelectorTextContains('main p', '2 events');
        $client->request('GET', '/events');
        self::assertSelectorTextContains('main p', '2 мероприятия');
    }
}
