<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\Profile;
use App\Form\Admin\ProfileType;
use App\Service\Importer\ProfileImporter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProfileController
 * @package App\Controller\Admin
 * @Route("/admin/profiles")
 */
class ProfileController extends AbstractController
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="profile_list")
     */
    public function list(Request $request, PaginatorInterface $paginator): Response
    {
        $qb = $this->em->getRepository(Profile::class)->getAllQueryQB();

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            100
        );

        return $this->render('admin/profiles/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/create", name="profile_create")
     */
    public function create(Request $request): Response
    {
        $form = $this->createForm(ProfileType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $profile = $form->getData();

            $this->em->persist($profile);
            $this->em->flush();

            return $this->redirectToRoute('profile_edit', ['id' => $profile->getId()]);
        }

        return $this->render('admin/profiles/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="profile_edit")
     * @ParamConverter("profile", class="App\Entity\Profile")
     */
    public function edit(Request $request, Profile $profile): Response
    {
        $form = $this->createForm(ProfileType::class, $profile);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $profile = $form->getData();

            $this->em->persist($profile);
            $this->em->flush();

            return $this->redirectToRoute('profile_edit', ['id' => $profile->getId()]);
        }

        return $this->render('admin/profiles/edit.html.twig', [
            'profile' => $profile,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="profile_delete")
     * @ParamConverter("profile", class="App\Entity\Profile")
     */
    public function delete(Profile $profile): Response
    {
        $this->em->remove($profile);
        $this->em->flush();

        return $this->redirectToRoute('profile_list');
    }

    /**
     * @Route("/import/{eventId}", name="profile_import")
     * @ParamConverter("event", class="App\Entity\Event", options={"mapping": {"eventId": "id"}})
     */
    public function importProfiles(Event $event, ProfileImporter $importer)
    {
        $source = $event->getRaces()->first()->getRaceResultsSource()->getType();

        $success = $importer->import($event->getSlug(), $source);

        return $this->redirectToRoute('event_list');

    }
}
