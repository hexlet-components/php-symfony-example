<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Process\Process;

final class AgendaTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        new Process([PHP_BINARY, 'bin/reset.php', 'test'], dirname(__DIR__), ['APP_ENV' => 'test'])->mustRun();
    }

    private function clientFor(string $email): KernelBrowser
    {
        $client = static::createClient();
        $user = self::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
        $client->loginUser($user);
        return $client;
    }

    private function eventId(): int
    {
        return self::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Event::class)
            ->findOneBy(['title' => 'Встреча PHP-сообщества'])
            ->getId();
    }

    private function countEvents(): int
    {
        return self::getContainer()->get(EntityManagerInterface::class)->getRepository(Event::class)->count([]);
    }

    public function testPublicPagesAndMissingRecord(): void
    {
        $client = static::createClient();
        $client->request('GET', '/events');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Афиша мероприятий');
        $id = $this->eventId();
        $client->request('GET', '/events/' . $id);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Встреча PHP-сообщества');
        self::assertSelectorNotExists('form[action$="/delete"]');
        $client->request('GET', '/events/999999');
        self::assertResponseStatusCodeSame(404);
    }

    public function testGuestIsRedirectedForEveryWriteAction(): void
    {
        $client = static::createClient();
        foreach ([
            ['GET',  '/events/new'],
            ['POST', '/events/new'],
            ['GET',  '/events/1/edit'],
            ['POST', '/events/1/edit'],
            ['POST', '/events/1/delete'],
        ] as [$method, $path]) {
            $client->request($method, $path);
            self::assertResponseRedirects('http://localhost/login');
        }
        self::assertSame(2, $this->countEvents());
    }

    public function testValidCreateUsesCurrentUserAndRedirects(): void
    {
        $client = $this->clientFor('alice@example.test');
        $crawler = $client->request('GET', '/events/new');
        $form = $crawler->selectButton('Сохранить')->form();
        $client->submit($form, [
            'event[title]' => 'Новая встреча',
            'event[description]' => 'Описание встречи',
            'event[startsAt]' => '2035-05-20T19:00',
        ]);
        self::assertResponseStatusCodeSame(303);
        self::assertSame(3, $this->countEvents());
        $event = self::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Event::class)
            ->findOneBy(['title' => 'Новая встреча']);
        self::assertSame('alice@example.test', $event->getOwner()->getEmail());
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Новая встреча');
    }

    public function testInvalidFormPreservesDatabaseAndInput(): void
    {
        $client = $this->clientFor('alice@example.test');
        $crawler = $client->request('GET', '/events/new');
        $client->submit($crawler->selectButton('Сохранить')->form(), [
            'event[title]' => '',
            'event[description]' => 'Сохранившееся описание',
            'event[startsAt]' => '2035-05-20T19:00',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('form[name="event"]', 'Значение не должно быть пустым');
        self::assertSelectorTextContains('textarea', 'Сохранившееся описание');
        self::assertSame(2, $this->countEvents());
    }

    public function testWrongCsrfWithCorrectOriginCannotCreate(): void
    {
        $client = $this->clientFor('alice@example.test');
        $crawler = $client->request('GET', '/events/new');
        $values = $crawler->selectButton('Сохранить')->form()->getPhpValues();
        $values['event']['title'] = 'Подмена токена';
        $values['event']['description'] = 'Описание';
        $values['event']['startsAt'] = '2035-05-20T19:00';
        // Учебные пароли и ошибочные токены проверяют сценарий входа или отказа.
        // @mago-expect lint:no-literal-password
        $values['event']['_token'] = 'wrong-token';
        $client->request('POST', '/events/new', $values, [], ['HTTP_ORIGIN' => 'http://localhost']);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('form[name="event"]', 'CSRF значение недопустимо');
        self::assertSame(2, $this->countEvents());
    }

    public function testOwnerFieldCannotBeForged(): void
    {
        $client = $this->clientFor('alice@example.test');
        $crawler = $client->request('GET', '/events/new');
        $values = $crawler->selectButton('Сохранить')->form()->getPhpValues();
        $values['event']['title'] = 'Подмена владельца';
        $values['event']['description'] = 'Описание';
        $values['event']['startsAt'] = '2035-05-20T19:00';
        $values['event']['owner'] = '2';
        $client->request('POST', '/events/new', $values);
        self::assertResponseStatusCodeSame(422);
        self::assertSame(2, $this->countEvents());
    }

    public function testOwnerCanEditWithoutAddingARecord(): void
    {
        $client = $this->clientFor('alice@example.test');
        $id = $this->eventId();
        $crawler = $client->request('GET', '/events/' . $id . '/edit');
        $client->submit($crawler->selectButton('Сохранить')->form(), ['event[title]' => 'Изменённая встреча']);
        self::assertResponseStatusCodeSame(303);
        self::assertSame(2, $this->countEvents());
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Изменённая встреча');
    }

    public function testOtherUserCannotEditOrDeleteEvenWithOwnToken(): void
    {
        $client = $this->clientFor('bob@example.test');
        $id = $this->eventId();
        foreach (['GET', 'POST'] as $method) {
            $client->request($method, '/events/' . $id . '/edit');
            self::assertResponseStatusCodeSame(403);
        }
        // Учебные пароли и ошибочные токены проверяют сценарий входа или отказа.
        // @mago-expect lint:no-literal-password
        $client->request('POST', '/events/' . $id . '/delete', ['_token' => 'wrong']);
        self::assertResponseStatusCodeSame(403);
        self::assertSame(2, $this->countEvents());
    }

    public function testAdminCanEditOthersEvent(): void
    {
        $client = $this->clientFor('admin@example.test');
        $id = $this->eventId();
        $crawler = $client->request('GET', '/events/' . $id . '/edit');
        $client->submit($crawler->selectButton('Сохранить')->form(), ['event[title]' => 'Правка администратора']);
        self::assertResponseStatusCodeSame(303);
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Правка администратора');
    }

    public function testDeleteRequiresPostAndValidToken(): void
    {
        $client = $this->clientFor('alice@example.test');
        $id = $this->eventId();
        $client->request('GET', '/events/' . $id . '/delete');
        self::assertResponseStatusCodeSame(405);
        $client->request(
            'POST',
            '/events/' . $id . '/delete',
            // Учебные пароли и ошибочные токены проверяют сценарий входа или отказа.
            // @mago-expect lint:no-literal-password
            ['_token' => 'wrong'],
            [],
            ['HTTP_ORIGIN' => 'http://localhost'],
        );
        self::assertResponseStatusCodeSame(403);
        self::assertSame(2, $this->countEvents());
        $crawler = $client->request('GET', '/events/' . $id);
        $client->submit($crawler->selectButton('Удалить')->form());
        self::assertResponseStatusCodeSame(303);
        self::assertSame(1, $this->countEvents());
        $client->request('GET', '/events/' . $id);
        self::assertResponseStatusCodeSame(404);
    }

    public function testLoginWrongPasswordAndRealLoginLogoutWithCsrf(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $client->submit($crawler->selectButton('Войти')->form(), [
            '_username' => 'alice@example.test',
            // Учебные пароли и ошибочные токены проверяют сценарий входа или отказа.
            // @mago-expect lint:no-literal-password
            '_password' => 'wrong',
        ]);
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertSelectorExists('[role="alert"]');
        $client->submit($crawler->selectButton('Войти')->form(), [
            '_username' => 'alice@example.test',
            // Учебные пароли и ошибочные токены проверяют сценарий входа или отказа.
            // @mago-expect lint:no-literal-password
            '_password' => 'study-password',
        ]);
        self::assertResponseRedirects('/events');
        $crawler = $client->followRedirect();
        self::assertSelectorTextContains('nav', 'alice@example.test');
        $values = $crawler->selectButton('Выйти')->form()->getPhpValues();
        // Учебные пароли и ошибочные токены проверяют сценарий входа или отказа.
        // @mago-expect lint:no-literal-password
        $client->request('POST', '/logout', ['_csrf_token' => 'wrong'], [], ['HTTP_ORIGIN' => 'http://localhost']);
        self::assertResponseStatusCodeSame(403);
        $client->request('POST', '/logout', $values);
        self::assertResponseRedirects('/events');
        $client->followRedirect();
        self::assertSelectorNotExists('button:contains("Выйти")');
    }

    public function testWrongLoginCsrfWithCorrectOriginRejectsLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $client->request(
            'POST',
            '/login',
            // Учебные пароли и ошибочные токены проверяют сценарий входа или отказа.
            // @mago-expect lint:no-literal-password(2)
            ['_username' => 'alice@example.test', '_password' => 'study-password', '_csrf_token' => 'wrong'],
            [],
            ['HTTP_ORIGIN' => 'http://localhost'],
        );
        self::assertResponseRedirects('/login');
        $client->followRedirect();
        self::assertSelectorExists('[role="alert"]');
        $client->request('GET', '/events/new');
        self::assertResponseRedirects('http://localhost/login');
    }
}
