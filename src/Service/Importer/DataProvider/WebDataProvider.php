<?php

namespace App\Service\Importer\DataProvider;

use App\Entity\RaceResultsSource;
use App\Service\ResultPageParsers\WebDataParserInterface;
use App\Service\WebDownloader;

abstract class WebDataProvider
{
    protected array $results = [];

    protected WebDownloader $webDownloader;
    protected WebDataParserInterface $parser;

    protected function getResults(RaceResultsSource $resultsSource)
    {
        $link = $resultsSource->getLink();

        if (empty($this->results[$link])) {
            $html = $this->webDownloader->getHtml($link);
            $this->results[$link] = $this->parser->parse($html, $resultsSource);
        }

        return $this->results[$link];
    }
}
