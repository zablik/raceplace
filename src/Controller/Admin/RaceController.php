<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\Race;
use App\Form\Admin\RaceType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RaceController
 * @package App\Controller\Admin
 * @Route("/admin/races")
 */
class RaceController extends AbstractController
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/{eventId}", name="race_list")
     * @ParamConverter("event", class="App\Entity\Event", options={"mapping": {"eventId": "id"}})
     */
    public function list(Event $event): Response
    {
         return $this->render('admin/races/list.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @Route("/{eventId}/create", name="race_create")
     * @ParamConverter("event", class="App\Entity\Event", options={"mapping": {"eventId": "id"}})
     */
    public function create(Request $request, Event $event): Response
    {
        $form = $this->createForm(RaceType::class, null, [
            'event' => $event,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $race = $form->getData();

            $this->em->persist($race);
            $this->em->flush();

            return $this->redirectToRoute('race_edit', [
                'eventId' => $event->getId(),
                'id' => $race->getId()
            ]);
        }

        return $this->render('admin/races/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{eventId}/edit/{id}", name="race_edit")
     * @ParamConverter("event", class="App\Entity\Event", options={"mapping": {"eventId": "id"}})
     * @ParamConverter("race", class="App\Entity\Race")
     */
    public function edit(Request $request, Event $event, Race $race): Response
    {
        $form = $this->createForm(RaceType::class, $race, [
            'event' => $event
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $race = $form->getData();

            $this->em->persist($race);
            $this->em->flush();

            return $this->redirectToRoute('race_list', ['eventId' => $event->getId()]);
        }

        return $this->render('admin/races/edit.html.twig', [
            'event' => $event,
            'race' => $race,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{eventId}/duplicate/{id}", name="race_duplicate")
     * @ParamConverter("event", class="App\Entity\Event", options={"mapping": {"eventId": "id"}})
     * @ParamConverter("race", class="App\Entity\Race")
     */
    public function duplicate(Event $event, Race $race)
    {
        $duplicate = clone $race;
        $duplicate->setSlug($race->getSlug() . ' clone ' . uniqid());
        $this->em->persist($duplicate);
        $this->em->flush();

        return $this->redirectToRoute('race_list', ['eventId' => $event->getId()]);
    }

    /**
     * @Route("/{eventId}/delete/{id}", name="race_delete")
     * @ParamConverter("event", class="App\Entity\Event", options={"mapping": {"eventId": "id"}})
     * @ParamConverter("race", class="App\Entity\Race")
     */
    public function delete(Event $event, Race $race): Response
    {
        $this->em->remove($race);
        $this->em->flush();

        return $this->redirectToRoute('race_list', ['eventId' => $event->getId()]);
    }
}
