<?php

namespace App\Service\ResultPageParsers\OBelarus\DTO;

class CheckpointsTableRow
{
    public string $checkpointMark;
    public ?float $distance;
    public int $time;
    public int $totalTime;
    public ?float $speed;
    public ?float $pace;
}
