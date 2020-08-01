<?php

namespace App\Service\ResultPageParsers\OBelarus\DTO;

class Race
{
    public string $type;
    public string $distance;

    /** @var ResultsTable */
    public ?ResultsTable $results;
}
