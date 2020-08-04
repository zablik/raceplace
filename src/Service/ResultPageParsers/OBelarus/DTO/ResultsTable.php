<?php

namespace App\Service\ResultPageParsers\OBelarus\DTO;

class ResultsTable
{
    public string $code;
    public string $group;

    /** @var ResultsTableRow[] */
    public array $results = [];
}
