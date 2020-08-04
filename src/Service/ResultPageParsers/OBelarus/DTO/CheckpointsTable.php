<?php

namespace App\Service\ResultPageParsers\OBelarus\DTO;

class CheckpointsTable
{
    public string $name;
    public string $numberPlate;
    public string $code;

    /** @var CheckpointsTableRow[] */
    public array $checkpoints = [];
}
