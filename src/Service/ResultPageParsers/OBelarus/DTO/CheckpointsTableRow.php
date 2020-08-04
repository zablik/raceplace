<?php

namespace App\Service\ResultPageParsers\OBelarus\DTO;

use \DateTime;

class CheckpointsTableRow
{
    public string $mark = '';
    public ?float $distance;
    public ?DateTime $time;
    public ?DateTime $totalTime;
    public ?float $speed;
    public ?DateTime $pace;
}
