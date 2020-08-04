<?php

namespace App\Service\Importer\DataProvider\Profile;

use App\Service\Importer\Exception\DataProviderExcepton;

class ProfileDataProviderHub
{
    const OBELARUS = 'obelarus';
    const STRAVA = 'strava';

    private ObelarusProfileDataProvider $obelarusProvider;

    public function __construct(ObelarusProfileDataProvider $obelarusProvider)
    {
        $this->obelarusProvider = $obelarusProvider;
    }

    private function providerMap()
    {
        return [
            self::OBELARUS => $this->obelarusProvider
        ];
    }

    public function getProvider(string $source): ProfileDataProviderInterface
    {
        if (empty($this->providerMap()[$source])) {
            throw new DataProviderExcepton(sprintf('Unexpected data source "%s"', $source));
        }

        return $this->providerMap()[$source];
    }
}
