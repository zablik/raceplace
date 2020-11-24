<?php

namespace App\Service\ResultPageParsers\Arf;

use App\Entity\RaceResultsSource;
use App\Service\ResultPageParsers\Exception\ParseResultsException;
use App\Service\ResultPageParsers\DTO\ResultsTable;
use App\Service\ResultPageParsers\DTO\ResultsTableRow;
use App\Service\ResultPageParsers\WebDataParserInterface;
use Symfony\Component\DomCrawler\Crawler;

class WebResultsTableParser implements WebDataParserInterface
{
    /**
     * @param string $html
     * @param RaceResultsSource $resultsSource
     * @return ResultsTable[]
     */
    public function parse(string $html, RaceResultsSource $resultsSource): array
    {
        $results = (new Crawler($html))
            ->filterXPath("//div[contains(@id, 'game-results-table-')]//h5")
            ->each(function (Crawler $parentCrawler, $i) {
                $group = TableConfig::titleToGroupConverter($parentCrawler->text());

                if ($group) {
                    $resultsTable = new ResultsTable();
                    $resultsTable->group = $group;
                    $resultsTable->results = $this->parseResultsTable(
                        $parentCrawler
                            ->filterXPath("node()/following-sibling::div[contains(@id, 'results-')]/table")
                            ->eq(0)
                    );

                    return $resultsTable;
                }
            });

        $results = array_filter($results, fn(?ResultsTable $table) => !empty($table->group) && !empty($table->results));

        return $results;
    }

    private function parseResultsTable(Crawler $node)
    {
        return $node->filterXPath("node()/tr[td[contains(@id, 'res.') and not(contains(@id, '.header.'))]]")->each(
            function (Crawler $parentCrawler, $i) {
                $row = new ResultsTableRow();

                $row->place = $parentCrawler->filterXPath("node()/td[contains(@id, 'res.place-')]")->text();
                $row->numberPlate = $parentCrawler->filterXPath("node()/td[contains(@id, 'res.num-')]")->text();
                $row->name = $parentCrawler->filterXPath("node()/td[contains(@id, 'res.team-')]//strong")->text();
                $row->yearBorn = $parentCrawler->filterXPath("node()/td[contains(@id, 'res.birthyear-')]")->text();
                $row->regionClub = $parentCrawler->filterXPath("node()/td[contains(@id, 'res.geo-')]")->text();
                $row->distance = $parentCrawler->filterXPath("node()/td[contains(@id, 'res.points-sum-')]")->text();
                $row->time = $parentCrawler->filterXPath("node()/td[contains(@id, 'res.time-sum-')]")->text();
                $profileLinkNode = $parentCrawler
                    ->filterXPath("node()/td[contains(@id, 'res.control-')]//a[img[contains(@alt, 'Личная страница')]]");

                if ($profileLinkNode->count() > 0) {
                    $userLink = $profileLinkNode->attr('href');

                    if ($userLink && preg_match('/&id=(\d+)$/', $userLink, $match)) {
                        $row->arfId = $match[1];
                    }
                }

                return $row;
            }
        );
    }
}
