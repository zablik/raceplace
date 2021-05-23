<?php

namespace App\Controller\Rating;

use App\Entity\Profile;
use App\Entity\Race;
use App\Service\Rating\RatingManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class IndexController
 * @package App\Rating\Controller
 * @Route("/rating")
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request, RatingManager $ratingManager, EntityManagerInterface $em)
    {
        $type = $request->get('type', 'xcm');
        $minDistance = $request->get('min-distance', 0);
        $maxDistance = $request->get('max-distance', 1e4);
        $from = $request->get('from', '2019-01-01');
        $till = $request->get('till', 'now');

        $races = $em->getRepository(Race::class)->findByFilter(
            $type,
            $minDistance,
            $maxDistance,
            new \DateTime($from),
            new \DateTime($till)
        );

        $rating = $ratingManager->generateRating($races);



        $profiles = $em->getRepository(Profile::class)->getForRating(array_keys($rating), $races);

        $stat = array_reduce($rating, function ($stat, float $score) {
            $group = strval(round($score * 4) / 4);
            if (!isset($stat[$group])) {
                $stat[$group] = 0;
            }
            $stat[$group]++;

            return $stat;
        }, []);

//        var_dump($stat);
//        die();

        return $this->render('rating/index.html.twig', [
            'stat' => $stat,
            'rating' => $rating,
            'profiles' => $profiles,
        ]);
    }
}

