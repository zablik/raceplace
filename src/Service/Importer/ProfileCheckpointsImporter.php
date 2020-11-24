<?php

namespace App\Service\Importer;

use App\Entity\Checkpoint;
use App\Entity\ProfileCheckpoint;
use App\Entity\ProfileResult;
use App\Repository\EventRepository;
use App\Repository\ProfileRepository;
use App\Service\Importer\DataProvider\ProfileCheckpoints\ProfileCheckpointsDataProviderHub;
use App\Service\ResultPageParsers\OBelarus\DTO\CheckpointsTableRow;
use App\Service\Utils\CollectionUtils;
use App\Service\Utils\MotionUtils;
use App\Service\Utils\NumberUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProfileCheckpointsImporter
{
    protected ProfileCheckpointsDataProviderHub $providerHub;
    protected EntityManagerInterface $em;
    protected SerializerInterface $serializer;
    protected EventRepository $eventRepository;
    protected ProfileRepository $profileRepository;

    public function __construct(
        ProfileCheckpointsDataProviderHub $providerHub,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        EventRepository $eventRepository,
        ProfileRepository $profileRepository
    ) {
        $this->providerHub = $providerHub;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->eventRepository = $eventRepository;
        $this->profileRepository = $profileRepository;
    }

    public function import(string $eventSlug, string $source)
    {
        $event = $this->eventRepository->findWithRaces($eventSlug);

        if (!$event) {
            throw new \Exception(sprintf('No event found for slug "%s"', $eventSlug));
        }

        foreach ($event->getRaces() as $race) {
            $profileCheckpointsTables = $this->providerHub->getProvider($source)->getProfileCheckpointsData($race);

            foreach ($profileCheckpointsTables as $table) {
                /** @var ProfileResult $profileResult */
                $profileResult = $race->getProfileResults()->filter(
                    fn(ProfileResult $profileResult) => $profileResult->getNumberPlate() === $table->numberPlate
                )->first();

                if (!$profileResult) {
                    foreach ($event->getRaces() as $race) {
                        /** @var ProfileResult $profileResult */
                        $profileResult = $race->getProfileResults()->filter(
                            fn(ProfileResult $profileResult) => $profileResult->getNumberPlate() === $table->numberPlate
                        )->first();

                        if ($profileResult) {
                            break;
                        }
                    }

                    // TODO: #log data inconsistency
//                    throw new DataProviderExcepton(sprintf(
//                        'No profile result found for the Number Plate "%s" in Race "%s" (ID#%d)',
//                        $table->numberPlate,
//                        $race->getSlug(),
//                        $race->getId()
//                    ));
                }

                $profileCheckpoints = [];
                foreach ($table->checkpoints as $checkpointRow) {
                    $checkpoint = $race->getCheckpoints()->filter(
                        function (Checkpoint $checkpoint) use ($checkpointRow) {
                            return NumberUtils::floatEq($checkpoint->getDistance(), $checkpointRow->distance)
                                || $checkpoint->getMark() === $checkpointRow->mark;
                        }
                    )->first();

                    $profileCheckpoint = $this->toProfileCheckpoint($checkpointRow);
                    $profileCheckpoint->setCheckpoint($checkpoint);
                    $profileCheckpoint->setProfileResult($profileResult);
                    $profileCheckpoint = MotionUtils::recalculateProfileCheckpoint($profileCheckpoint);

                    $profileCheckpoints[] = $profileCheckpoint;
                }

                CollectionUtils::importCollection(
                    $profileResult,
                    $profileResult->getCheckpoints()->getValues(),
                    $profileCheckpoints,
                    'Checkpoint',
                    fn(ProfileCheckpoint $c1, ProfileCheckpoint $c2) => $this->compareProfileCheckpoints($c1, $c2),
                    ['time', 'totalTime', 'speed', 'pace']
                );
            }

            $this->em->persist($race);
        }

        $this->em->flush();
    }

    private function compareProfileCheckpoints(ProfileCheckpoint $c1, ProfileCheckpoint $c2)
    {
        return $c1->getCheckpoint()->getId() === $c2->getCheckpoint()->getId()
            && $c1->getProfileResult()->getId() === $c2->getProfileResult()->getId();
    }

    private function toProfileCheckpoint(CheckpointsTableRow $checkpointsTableRow)
    {
        return (new ProfileCheckpoint())
            ->setTime($checkpointsTableRow->time)
            ->setTotalTime($checkpointsTableRow->totalTime);
    }
}
