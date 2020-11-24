<?php

namespace App\Service\Importer\DataProvider\Profile;

use App\Service\ResultPageParsers\DTO\Profile;

interface ProfileDataProviderInterface
{
    /**
     * @param string $eventSlug
     * @return Profile[]
     */
    public function getProfilesData(string $eventSlug): array;
}