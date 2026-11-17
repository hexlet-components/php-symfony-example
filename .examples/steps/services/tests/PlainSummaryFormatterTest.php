<?php

namespace App\Tests;

use App\Service\PlainSummaryFormatter;
use PHPUnit\Framework\TestCase;

final class PlainSummaryFormatterTest extends TestCase
{
    public function testShortAndCyrillicText(): void
    {
        $formatter = new PlainSummaryFormatter();
        self::assertSame('Текст', $formatter->format('Текст'));
        self::assertSame(str_repeat('А', 80) . '…', $formatter->format(str_repeat('А', 81)));
    }
}
