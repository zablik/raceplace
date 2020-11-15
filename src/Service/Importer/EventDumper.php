<?php

namespace App\Service\Importer;

use App\Entity\Event;
use App\Entity\Race;
use App\Service\Importer\DataProvider\Race\YamlRaceDataProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class EventDumper
{
    protected string $sourceDir;

    public function __construct(string $kernelRootDir)
    {
        $this->sourceDir = $kernelRootDir . YamlRaceDataProvider::RACE_DATE_PATH;
    }

    public function dumpExists(Event $event): bool
    {
        $filename = $this->getEventDumpFilename($event);
        $filesystem = new Filesystem();

        return $filesystem->exists($filename);
    }

    public function dump(Event $event)
    {
        $config = $this->generateYamlConfig($event);
        $filePath = $this->getEventDumpFilename($event);

        $filesystem = new Filesystem();
        $filesystem->dumpFile($filePath, $config);
    }

    private function generateYamlConfig(Event $event)
    {
        $eventData = [
            $event->getSlug() => [
                'name' => $event->getName(),
                'slug' => $event->getSlug(),
                'date' => $event->getDate()->format('d.m.Y'),
                'link' => $event->getLink(),
            ]
        ];

        $eventData[$event->getSlug()]['races'] = array_map(
            function (Race $race) {
                return [
                    'slug' => $race->getSlug(),
                    'type' => $race->getType(),
                    'distance' => $race->getDistance(),
                    'resultsSource' => [
                        'type' => $race->getResultsSource()->getType(),
                        'link' => $race->getResultsSource()->getLink(),
                        'table_config_type' => $race->getResultsSource()->getTableConfigType(),
                        'codes' => $race->getResultsSource()->getCodes()
                    ],
                ];
            },
            $event->getRaces()->toArray()
        );

        return Yaml::dump($eventData, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }

    private function getEventDumpFilename(Event $event)
    {
        $filename = str_replace('-', '_', $event->getSlug()) . '.yaml';

        return $this->sourceDir . '/' . $filename;
    }
}
