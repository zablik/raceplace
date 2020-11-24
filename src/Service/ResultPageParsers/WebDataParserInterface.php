<?php

namespace App\Service\ResultPageParsers;

use App\Entity\RaceResultsSource;

interface WebDataParserInterface
{
    public function parse(string $html, RaceResultsSource $resultsSource): array;
}
