<?php

namespace App\Tests;

use App\Service\PlainSummaryFormatter;
use PHPUnit\Framework\TestCase;

final class PlainSummaryFormatterTest extends TestCase
{
    public function testShortAndCyrillicText(): void
    {
        $formatter = new PlainSummaryFormatter(5);
        self::assertSame('Текст', $formatter->format('Текст'));
        self::assertSame('Обсуж…', $formatter->format('Обсуждаем встречу'));
    }
}
