<?php

namespace App\Service\Importer\DataProvider\RaceResults;

use App\Service\Importer\Exception\DataProviderExcepton;

class RaceResultsDataProviderHub
{
    const ARF = 'arf';
    const OBELARUS = 'obelarus';
    const STRAVA = 'strava';

    private ObelarusRaceResultsDataProvider $obelarusProvider;
    private ArfRaceResultsDataProvider $arfProvider;

    public function __construct(
        ObelarusRaceResultsDataProvider $obelarusProvider,
        ArfRaceResultsDataProvider $arfProvider
    )
    {
        $this->obelarusProvider = $obelarusProvider;
        $this->arfProvider = $arfProvider;
    }

    private function providerMap()
    {
        return [
            self::OBELARUS => $this->obelarusProvider,
            self::ARF => $this->arfProvider,
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
