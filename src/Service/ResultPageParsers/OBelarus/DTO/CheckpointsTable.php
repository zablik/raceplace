<?php

namespace App\Service\ResultPageParsers\OBelarus\DTO;

class CheckpointsTable
{
    public string $name;
    public string $numberPlate;
    public string $raceCode;

    /** @var ResultsTableRow[] */
    public array $checkpoints;
}
