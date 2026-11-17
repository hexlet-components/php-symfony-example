<?php

declare(strict_types=1);

namespace App\Service;

interface SummaryFormatterInterface
{
    public function format(string $text): string;
}
