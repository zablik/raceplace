<?php

namespace App\Service\ResultPageParsers\DTO;

class ResultsTable
{
    public string $code;
    public string $group;

    /** @var ResultsTableRow[] */
    public array $results = [];
}
