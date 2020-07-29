<?php

namespace App\Service\ResultPageParsers\OBelarus\DTO;

class Event
{
    public string $name;

    public \DateTime $date;

    public string $link;

    /**
     * @var ResultsTable[]
     */
    public array $races = [];
}
