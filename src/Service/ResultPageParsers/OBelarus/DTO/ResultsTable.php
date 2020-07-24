<?php

namespace App\Service\ResultPageParsers\OBelarus\DTO;

class ResultsTable
{
    public string $type;
    public string $distance;
    public string $group;

    /** @var ResultsTableRow[] */
    public array $results;
}
