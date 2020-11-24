<?php

namespace App\Service\Importer\DataProvider\Profile;

use App\Service\Importer\Exception\DataProviderExcepton;

class ProfileDataProviderHub
{
    const ARF = 'arf';
    const OBELARUS = 'obelarus';
    const STRAVA = 'strava';

    private ObelarusProfileDataProvider $obelarusProvider;
    private ArfProfileDataProvider $arfProvider;

    public function __construct(
        ObelarusProfileDataProvider $obelarusProvider,
        ArfProfileDataProvider $arfProvider
    ) {
        $this->obelarusProvider = $obelarusProvider;
        $this->arfProvider = $arfProvider;
    }

    private function providerMap()
    {
        return [
            self::ARF => $this->arfProvider,
            self::OBELARUS => $this->obelarusProvider,
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
