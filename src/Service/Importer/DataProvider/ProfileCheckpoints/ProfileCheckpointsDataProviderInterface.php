<?php

namespace App\Service\Importer\DataProvider\ProfileCheckpoints;

use App\Entity\Race;
use App\Service\ResultPageParsers\OBelarus\DTO\CheckpointsTable;

interface ProfileCheckpointsDataProviderInterface
{
    /**
     * @param Race $race
     * @return CheckpointsTable[]
     */
    public function getProfileCheckpointsData(Race $race): array;
}