<?php

namespace App\Service\Importer\DataProvider\Race;

interface RaceDataProviderInterface
{
    public function getEventRacesData(string $eventSlug): array;
}