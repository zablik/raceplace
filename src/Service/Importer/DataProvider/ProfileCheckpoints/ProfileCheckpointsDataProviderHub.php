<?php

namespace App\Service\Importer\DataProvider\ProfileCheckpoints;

use App\Service\Importer\Exception\DataProviderExcepton;

class ProfileCheckpointsDataProviderHub
{
    const OBELARUS = 'obelarus';
    const STRAVA = 'strava';

    private ObelarusProfileCheckpointsDataProvider $obelarusProvider;

    public function __construct(ObelarusProfileCheckpointsDataProvider $obelarusProvider)
    {
        $this->obelarusProvider = $obelarusProvider;
    }

    private function providerMap()
    {
        return [
            self::OBELARUS => $this->obelarusProvider
        ];
    }

    public function getProvider(string $source): ProfileCheckpointsDataProviderInterface
    {
        if (empty($this->providerMap()[$source])) {
            throw new DataProviderExcepton(sprintf('Unexpected data source "%s"', $source));
        }

        return $this->providerMap()[$source];
    }
}
