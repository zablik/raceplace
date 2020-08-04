<?php

namespace App\Service\Importer\DataProvider;

use App\Service\ResultPageParsers\OBelarus\WebDataParserInterface;
use App\Service\WebDownloader;

abstract class WebDataProvider
{
    protected array $results = [];

    protected WebDownloader $webDownloader;
    protected WebDataParserInterface $parser;

    protected function getResults(string $link)
    {
        if (empty($this->results[$link])) {
            $html = $this->webDownloader->getHtml($link);
            $this->results[$link] = $this->parser->parse($html);
        }

        return $this->results[$link];
    }
}
