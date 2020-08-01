<?php

namespace App\Service\ResultPageParsers\OBelarus\DTO;

class ResultsTableRow
{
    public string $name;
    public ?string $regionClub;
    public ?int $yearBorn;
    public string $numberPlate;
    public ?float $distance;
    public ?int $time;
    public ?int $place;
    public string $note;
    public bool $disqualification = false;
}
