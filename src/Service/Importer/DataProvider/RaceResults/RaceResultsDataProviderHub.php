<?php

namespace App\Service\Importer\DataProvider\RaceResults;

use App\Service\Importer\Exception\DataProviderExcepton;

class RaceResultsDataProviderHub
{
    const OBELARUS = 'obelarus';
    const STRAVA = 'strava';

    private ObelarusRaceResultsDataProvider $obelarusProvider;

    public function __construct(ObelarusRaceResultsDataProvider $obelarusProvider)
    {
        $this->obelarusProvider = $obelarusProvider;
    }

    private function providerMap()
    {
        return [
            self::OBELARUS => $this->obelarusProvider
        ];
    }

    public function getProvider(string $source): RaceResultsDataProviderInterface
    {
        if (empty($this->providerMap()[$source])) {
            throw new DataProviderExcepton(sprintf('Unexpected data source "%s"', $source));
        }

        return $this->providerMap()[$source];
    }
}
