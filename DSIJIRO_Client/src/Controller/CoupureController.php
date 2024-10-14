<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ZoneCoupeeService;
use App\Util\Util;
use App\Entity\Coupure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\SecteurElectrique;
use App\Entity\PostElectrique;
use App\Entity\Lieu;
use App\Entity\Filtre;
use App\Entity\ZoneElectrique;
use App\Doctrine\PolygonType;

class CoupureController extends AbstractController
{
    private ZoneCoupeeService $zoneCoupeeService;

    public function __construct(ZoneCoupeeService $zoneCoupeeService)
    {
        $this->zoneCoupeeService = $zoneCoupeeService;
    }

    #[Route('/carte-coupure', name: 'map_coupure')]
    public function map_coupure(EntityManagerInterface $entityManager): Response
    {
        return $this->render('coupure/map.html.twig', [
            'controller_name' => 'CoupureController'
        ]);
    }

    #[Route('/coupure/visible', name: 'visible_coupure', methods: ['POST'])]
    public function getVisibleZoneCoupee(Request $request): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent()); // il y a {message: "hello world"} mais comment l extraire
        $jsoncoords = json_encode($data->coord);

        // $zoneCoupee = new zoneCoupee();
        // $zoneCoupee->setCoordByJson($jsoncoords);

        // $zoneCoupees = $em->getRepository(zoneCoupee::class)->getAllIntersect($zoneCoupee);
        // $zonesCoupee = $this->zoneCoupeeService->fetchZoneCoupeeByDate($data->date);
        $zonesCoupee = $this->zoneCoupeeService->fetchZoneCoupeeOnBoundsByDate($data->debut, $data->fin);

        // Combinaison des données dans une structure de données associative
        $data = [
            'zonesCoupee' => $zonesCoupee
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
        // return new JsonResponse("Hello world");
    }

    #[Route('/zone-electrique/recherche', name: 'search_zone', methods: ['POST'])]
    public function searchZone(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent()); // il y a {message: "hello world"} mais comment l extraire

        $zones = [];

        if(isset($data->coord)) {
            $jsoncoords = json_encode($data->coord);
            $wkt_coord = PolygonType::convertJsonToPolygonWKT($jsoncoords);
            $zones = $em->getRepository(ZoneElectrique::class)->getAllIntersect($wkt_coord);
        } else if(isset($data->ref)) {
            $filtre = new Filtre($data->page, $data->limit, null, null, null, null, '?', '?');
            $zones = $em->getRepository(ZoneElectrique::class)->findByMultiFilter($filtre, $data->ref, $data->group);
        } else {
            $postes = $data->postes;
            $zones = $em->getRepository(ZoneElectrique::class)->findByPostesLike($postes);
        }

        // Combinaison des données dans une structure de données associative
        $data = [
            'zones' => $zones,
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
        // return new JsonResponse("Hello world");
    }
}
