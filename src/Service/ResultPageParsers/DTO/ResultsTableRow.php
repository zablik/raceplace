<?php

namespace App\Service\ResultPageParsers\DTO;

class ResultsTableRow
{
    public string $name;
    public ?string $regionClub;
    public ?string $yearBorn;
    public string $numberPlate;
    public ?string $distance;
    public ?string $time;
    public ?string $place;
    public ?string $note = null;
    public ?string $arfId = null;
}
