<?php

namespace App\Service;

final class PlainSummaryFormatter
{
    public function format(string $text): string
    {
        $maxLength = 80;
        return mb_strlen($text) <= $maxLength ? $text : mb_substr($text, 0, $maxLength) . '…';
    }
}
