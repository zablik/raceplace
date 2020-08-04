<?php

namespace App\Service\Importer\DataProvider\Race;

use App\Service\Importer\Exception\DataProviderExcepton;

class RaceDataProviderHub
{
    const YAML = 'yaml';
    const STRAVA = 'strava';

    private YamlRaceDataProvider $yamlProvider;

    public function __construct(YamlRaceDataProvider $yamlProvider)
    {
        $this->yamlProvider = $yamlProvider;
    }

    private function providerMap()
    {
        return [
            self::YAML => $this->yamlProvider
        ];
    }

    public function getProvider(string $source): RaceDataProviderInterface
    {
        if (empty($this->providerMap()[$source])) {
            throw new DataProviderExcepton(sprintf('Unexpected data source "%s"', $source));
        }

        return $this->providerMap()[$source];
    }
}
