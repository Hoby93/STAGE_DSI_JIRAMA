<?php

namespace App\Controller;

use App\Entity\Filtre;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Infrastructure;
use App\Entity\TypeInfrastructure;
use App\Repository\TypeInfrastructureRepository;
use App\Util\Util;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class InfraController extends AbstractController
{
    #[Route('/site/coordonnee-plus-proche', name: 'nearest_site_view', methods: ['POST'])]
    public function getNearestSiteView(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent());
        $views = $em->getRepository(Infrastructure::class)->findNearestOfUserPosition($data->latitude, $data->longitude);

        // Combinaison des données dans une structure de données associative
        $data = [
            'views' => $views
        ];

        // echo(new JsonResponse($infras));

        return new JsonResponse(Util::toJson($data));
        // return new JsonResponse("Hello world");
    }

    #[Route('/site/visible', name: 'visible_site', methods: ['POST'])]
    public function getVisibleinfra(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Décoder le contenu JSON du requette
        // $data = json_decode($request->getContent()); // il y a {message: "hello world"} mais comment l extraire
        // $jsoncoords = json_encode($data->coord);

        // $infra = new infra();
        // $infra->setCoordByJson($jsoncoords);

        // $infras = $em->getRepository(infra::class)->getAllIntersect($infra);
        $sites = $em->getRepository(Infrastructure::class)->findAll();

        // Combinaison des données dans une structure de données associative
        $data = [
            'sites' => $sites
        ];

        // echo(new JsonResponse($infras));

        return new JsonResponse(Util::toJson($data));
        // return new JsonResponse("Hello world");
    }
}
