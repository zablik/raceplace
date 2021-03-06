<?php

namespace App\Service\Importer\DataProvider\ProfileCheckpoints;

use App\Entity\Race;
use App\Repository\EventRepository;
use App\Service\Importer\DataProvider\WebDataProvider;
use App\Service\ResultPageParsers\OBelarus\DTO\CheckpointsTable;
use App\Service\ResultPageParsers\DTO\ResultsTableRow;
use App\Service\ResultPageParsers\OBelarus\WebCheckpointsParser;
use App\Service\WebDownloader;
use Doctrine\ORM\NonUniqueResultException;

class ObelarusProfileCheckpointsDataProvider extends WebDataProvider implements ProfileCheckpointsDataProviderInterface
{
    private EventRepository $eventRepository;

    public function __construct(
        WebDownloader $webDownloader,
        WebCheckpointsParser $parser,
        EventRepository $eventRepository
    ) {
        $this->webDownloader = $webDownloader;
        $this->parser = $parser;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param Race $race
     * @return ResultsTableRow[]
     * @throws NonUniqueResultException
     */
    public function getProfileCheckpointsData(Race $race): array
    {
        /** @var CheckpointsTable[] $tables */
        // TODO: config type for checkpoints
        $tables = $this->getResults($race->getResultsSource());

        return array_filter(
            $tables,
            fn(CheckpointsTable $table) => in_array($table->code, $race->getResultsSource()->getCodes())
        );
    }
}
