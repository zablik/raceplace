<?php

namespace App\Service\Importer\DataProvider\RaceResults;

use App\Entity\Race;
use App\Service\ResultPageParsers\DTO\ResultsTableRow;

interface RaceResultsDataProviderInterface
{
    /**
     * @param Race $race
     * @return ResultsTableRow[]
     */
    public function getRaceResultsData(Race $race): array;
}