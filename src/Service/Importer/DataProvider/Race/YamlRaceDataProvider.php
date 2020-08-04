<?php

namespace App\Service\Importer\DataProvider\Race;

use Symfony\Component\Yaml\Yaml;

class YamlRaceDataProvider implements RaceDataProviderInterface
{
    private string $sourceDir;

    public function __construct(string $kernelRootDir)
    {
        $this->sourceDir = $kernelRootDir . '/data/races';
    }

    public function getEventRacesData(string $eventSlug): array
    {
        $filename = str_replace('-', '_', $eventSlug);
        $source = sprintf('%s/%s.yaml', $this->sourceDir, $filename);
        $eventData = Yaml::parse(file_get_contents($source))['parameters'];

        // replace %params% with values
        array_walk_recursive($eventData, function (&$value) use ($eventData) {
            if (preg_match('/^%([\w\d\-]+)%$/', $value, $match)) {
                $value = $eventData[$match[1]];
            }
        });

        return $eventData[$eventSlug];
    }
}
