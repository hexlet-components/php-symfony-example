<?php

declare(strict_types=1);

namespace App\Service;

final class PlainSummaryFormatter implements SummaryFormatterInterface
{
    public function __construct(
        private readonly int $maxLength = 80,
    ) {}

    public function format(string $text): string
    {
        return mb_strlen($text) <= $this->maxLength ? $text : mb_substr($text, 0, $this->maxLength) . '…';
    }
}
