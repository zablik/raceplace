<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Form\Admin\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class EventController
 * @package App\Controller\Admin
 * @Route("/admin/events")
 */
class EventController extends AbstractController
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="event_list")
     */
    public function list(Request $request, PaginatorInterface $paginator): Response
    {
        $qb = $this->em->getRepository(Event::class)->getAllQueryQB();

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            30
        );

        return $this->render('admin/events/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/create", name="event_create")
     */
    public function create(Request $request): Response
    {
        $form = $this->createForm(EventType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();

            $this->em->persist($event);
            $this->em->flush();

            return $this->redirectToRoute('event_edit', ['id' => $event->getId()]);
        }

        return $this->render('admin/events/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="event_edit")
     * @ParamConverter("event", class="App\Entity\Event")
     */
    public function edit(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();

            $this->em->persist($event);
            $this->em->flush();

            return $this->redirectToRoute('event_edit', ['id' => $event->getId()]);
        }

        return $this->render('admin/events/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="event_delete")
     * @ParamConverter("event", class="App\Entity\Event")
     */
    public function delete(Event $event): Response
    {
        $this->em->remove($event);
        $this->em->flush();

        return $this->redirectToRoute('event_list');
    }
}
