<?php

namespace App\Service\ResultPageParsers\DTO;

class Profile
{
    public string $name;
    public ?string $regionClub;
    public ?string $yearBorn;
    public string $group;
    public ?string $stravaId = null;
    public ?string $arfId = null;
}
