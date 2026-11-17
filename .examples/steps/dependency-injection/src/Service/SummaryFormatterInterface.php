<?php

namespace App\Service;

interface SummaryFormatterInterface
{
    public function format(string $text): string;
}
