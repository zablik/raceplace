<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\ProfileResult;
use App\Entity\Race;
use App\Service\Importer\RaceResultsImporter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class RaceResultsController
 * @package App\Controller\Admin
 * @Route("/admin/race-results")
 */
class RaceResultsController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected RaceResultsImporter $importer;

    public function __construct(EntityManagerInterface $em, RaceResultsImporter $importer)
    {
        $this->em = $em;
        $this->importer = $importer;
    }

    /**
     * @Route("/{id}", name="race_results")
     * @ParamConverter("race", class="App\Entity\Race")
     */
    public function list(Race $race): Response
    {
        return $this->render('admin/race_results/list.html.twig', [
            'race' => $race,
        ]);
    }

    /**
     * @Route("/{id}/import", name="race_results_import")
     * @ParamConverter("race", class="App\Entity\Race")
     */
    public function importResults(Race $race): Response
    {
        $this->importer->importRaceResults($race);

        return $this->redirectToRoute('race_results', ['id' => $race->getId()]);
    }

    /**
     * @Route("/{eventId}/import-event", name="event_results_import")
     * @ParamConverter("event", class="App\Entity\Event", options={"mapping": {"eventId": "id"}})
     */
    public function importAllResults(Event $event): Response
    {
        $this->importer->import($event->getSlug());

        return $this->redirectToRoute('race_list', ['eventId' => $event->getId()]);
    }

    /**
     * @Route("/{id}/delete", name="race_results_delete")
     * @ParamConverter("race", class="App\Entity\Race")
     */
    public function delete(Race $race): Response
    {
        $race->getProfileResults()->map(fn(ProfileResult $result) => $this->em->remove($result));
        $this->em->flush();

        return $this->redirectToRoute('race_results', ['id' => $race->getId()]);
    }
}
