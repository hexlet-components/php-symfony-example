<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;

final class UpcomingEventsCommandTest extends KernelTestCase
{
    public function testOutputAndInvalidArgument(): void
    {
        new Process([PHP_BINARY, 'bin/reset.php', 'test'], dirname(__DIR__), ['APP_ENV' => 'test'])->mustRun();
        $application = new Application(self::bootKernel());
        $tester = new CommandTester($application->find('app:events:upcoming'));
        self::assertSame(0, $tester->execute(['limit' => '1']));
        self::assertStringContainsString('Встреча PHP-сообщества', $tester->getDisplay());
        self::assertStringNotContainsString('Вечер настольных игр', $tester->getDisplay());
        self::assertSame(2, $tester->execute(['limit' => '0']));
        self::assertSame(2, $tester->execute(['limit' => 'abc']));
    }
}
