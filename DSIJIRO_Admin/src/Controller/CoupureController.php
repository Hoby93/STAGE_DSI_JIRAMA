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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_OFFICER')]
class CoupureController extends AbstractController
{
    private ZoneCoupeeService $zoneCoupeeService;

    public function __construct(ZoneCoupeeService $zoneCoupeeService)
    {
        $this->zoneCoupeeService = $zoneCoupeeService;
    }

    #[Route('/coupure/suppression', name: 'delete_coupure', methods: ['POST'])]
    public function deleteCoupure(Request $request): JsonResponse
    {
        // Décoder le contenu JSON du requette
        //$data = json_decode($request->getContent()); // il y a {message: "hello world"} mais comment l extraire
        //$jsoncoords = json_encode($data->coord);

        // Combinaison des données dans une structure de données associative
        $data = [
            'status' => 1,
            'message' => 'Reussi'
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
        // return new JsonResponse("Hello world");
    }

    #[Route('/liste-coupure', name: 'list_coupure')]
    public function list_coupure(): Response
    {
        $zonesCoupee = $this->zoneCoupeeService->fetchZoneCoupee('desc');

        return $this->render('coupure/list.html.twig', [
            'controller_name' => 'CoupureController',
            'zonescoupee' => $zonesCoupee,
        ]);
    }

    #[Route('/liste-coupure/json', name: 'list_coupure_json')]
    public function list_coupure_json(Request $request): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent());
        $filtre = new Filtre($data->page, $data->limit, $data->niv, $data->motclee, $data->orderby, $data->orderinc, '?', '?');
        $filtre->setDateInterval($data->datedebut, $data->datefin);

        $zonesCoupee = $this->zoneCoupeeService->fetchZoneCoupeeByFilter($filtre);

        // Combinaison des données dans une structure de données associative
        $data = [
            'coupures' => $zonesCoupee
        ];

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/ajout-coupure', name: 'new_coupure')]
    public function new_infra(EntityManagerInterface $entityManager): Response
    { 
        $secteurs = $entityManager->getRepository(SecteurElectrique::class)->findAll();
        return $this->render('coupure/new.html.twig', [
            'controller_name' => 'CoupureController',
            'secteurs' => $secteurs,
        ]);
    }

    #[Route('/list-coupure/pdf/{coupures}', name: 'list_coupure_pdf')]
    public function modelPdfCoupure(?string $coupures = null): Response
    {
        $zonesCoupee = $this->zoneCoupeeService->fetchZoneCoupeeById($coupures, 'asc');

        return $this->render('coupure/pdf.model.html.twig', [
            'controller_name' => 'CoupureController',
            'zonescoupee' => $zonesCoupee,
        ]);
    }

    #[Route('/insertion-multiple-coupure', name: 'new_multi_coupure_do', methods: ['POST'])]
    public function insertMultiCoupure(Request $request, EntityManagerInterface $em)
    {
        $id_zones = $request->request->get('idzones');
        $coupure = new Coupure();
        $coupure->init(
            null, // id
            null, // ref
            null, // confid
            null, // date_saisie
            null, // date_annonce
            \DateTime::createFromFormat('Y-m-d\TH:i', $request->request->get('datedebut')),
            \DateTime::createFromFormat('Y-m-d\TH:i', $request->request->get('datefin')),
            $request->request->get('motif'),
            $request->request->get('division'),
            $request->request->get('sa'),
            1, // employeId
            1 // etat
        );

        // dump($coupure);
        // dump($id_zones);

        $em->persist($coupure); $em->flush(); // Inserer la coupure
        $id_coupure = $em->getRepository(Coupure::class)->getLastCoupureId();


        $em->getRepository(ZoneElectrique::class)->saveMultipleZoneCoupee($id_coupure, $id_zones); // Inserer la coupure_zone

        
        return $this->render('parametre/zone.list.html.twig', [
            'message' => '!Ok'
        ]);
    }

    #[Route('/insertion-coupure-et-zone', name: 'insert_coupure', methods: ['POST'])]
    public function insertCoupureAndZone(Request $request, EntityManagerInterface $em)
    {
        $coupure = new Coupure();
        $coupure->init(
            null, // id
            null, // ref
            null, // confidentialitee
            null, // date_saisie
            null, // date_annonce
            \DateTime::createFromFormat('Y-m-d\TH:i', $request->request->get('datedebut')),
            \DateTime::createFromFormat('Y-m-d\TH:i', $request->request->get('datefin')),
            $request->request->get('motif'),
            $request->request->get('division'),
            $request->request->get('sa'),
            1, // employeId
            1 // etat
        );
        $ref_secteur = $request->request->get('refsecteur');

        $bounds = PolygonType::findMinMaxCoordinates($request->request->get('coords'));
        
        $spec = ZoneElectrique::getSpecificities($bounds);
        $places = ZoneElectrique::getPlaceNames($bounds);

        $lieux = $places['places_str'];
        if($request->request->get('lieux') != "") {
            $lieux = $request->request->get('lieux');
        }

        $zone = new ZoneElectrique();
        $zone->init(
            null, // id_zone
            $ref_secteur,
            null, // ref_zone
            $spec['specificite'],
            $lieux,
            $request->request->get('postescoupee'),
            $spec['degree'], // degree
            null // Coord
        );

        // dump($coupure);
        // dump($zone);
        // dump($spec);
        // dump($lieux);

        $id_zone = $request->request->get('idzone');

        $em->getRepository(SecteurElectrique::class)->insertMissingSecteur($ref_secteur); // Inserer si secteur inexistant

        $id_secteur = $em->getRepository(SecteurElectrique::class)->findIdByRefSecteur($ref_secteur);
        //echo("id_zone: ".$id_zone."/id_secteur: ".$id_secteur);

        $coords = PolygonType::convertLatLngToPolygonWKT($request->request->get('coords'));
        $em->persist($coupure); $em->flush(); // Inserer la coupure

        $id_coupure = $em->getRepository(Coupure::class)->getLastCoupureId();

        if($id_zone == 0) {
            $em->getRepository(ZoneElectrique::class)->save($zone, $coords); // Inserer la zone
            $id_zone = $em->getRepository(ZoneElectrique::class)->getLastZoneElectriqueId();
        }

        $em->getRepository(PostElectrique::class)->insertMissingPosts($request->request->get('postescoupee'), $id_secteur);
        $em->getRepository(ZoneElectrique::class)->saveZonePost($request->request->get('postescoupee'), $id_zone); // Inserer la zone_post
        $em->getRepository(Lieu::class)->insertMissingPlace($places['places'], $id_secteur); // Inserer les nouveaux lieu
        $em->getRepository(ZoneElectrique::class)->saveZonePlace($places['places'], $id_zone); // Inserer la zone_place
        $em->getRepository(ZoneElectrique::class)->saveZoneCoupee($id_coupure, $id_zone); // Inserer la coupure_zone


        $secteurs = $em->getRepository(SecteurElectrique::class)->findAll();
        return $this->render('coupure/new.html.twig', [
            'controller_name' => 'CoupureController',
            'secteurs' => $secteurs,
        ]);
    }

    #[Route('/modifier-coupure', name: 'update_coupure', methods: ['POST'])]
    public function updateCoupure(Request $request, EntityManagerInterface $em)
    {
        // Récupération de l'entité existante
        $coupure = $em->getRepository(Coupure::class)->find($request->request->get('idcoupure'));
        if ($coupure) {
            // Modification des propriétés de l'entité existante
            $coupure->setDateDebut(\DateTime::createFromFormat('Y-m-d\TH:i', $request->request->get('datedebut')))
                    ->setDateFin(\DateTime::createFromFormat('Y-m-d\TH:i', $request->request->get('datefin')))
                    ->setMotif($request->request->get('motif'))
                    ->setDivision($request->request->get('division'))
                    ->setSa($request->request->get('sa'))
                    ->setEmployeId($request->request->get('employeid'))
                    ->setEtat($request->request->get('etat'));
        
            // Enregistrement des modifications
            $em->flush();
        }

        // dump($coupure);
        $em->persist($coupure); $em->flush(); // Modifier la coupure

        // $em->getRepository(ZoneElectrique::class)->saveMultipleZoneCoupee($id_coupure, $id_zones); // Inserer la coupure_zone

        
        return $this->render('coupure/list.html.twig');
    }

    // #[Route('/zone/nouvelle-prevision-coupure', name: 'new_coupure_by_zone', methods: ['POST'])]
    // public function insertCoupureByZone(Request $request, EntityManagerInterface $em)
    // {
    //     $coupure = new Coupure();
    //     $coupure->init(
    //         null, // id
    //         null, // ref
    //         1,
    //         null, // date_saisie
    //         null, // date_annonce
    //         \DateTime::createFromFormat('Y-m-d\TH:i', $request->request->get('datedebut')),
    //         \DateTime::createFromFormat('Y-m-d\TH:i', $request->request->get('datefin')),
    //         $request->request->get('motif'),
    //         $request->request->get('division'),
    //         $request->request->get('sa'),
    //         1, // employeId
    //         1 // etat
    //     );

    //     $id_zone = $request->request->get('idzone');
        
    //     $em->persist($coupure); $em->flush(); // Inserer la coupure

    //     $id_coupure = $em->getRepository(Coupure::class)->getLastCoupureId();

    //     $em->getRepository(ZoneElectrique::class)->saveZoneCoupee($id_coupure, $id_zone); // Inserer la coupure_zone

    //     $secteurs = $em->getRepository(SecteurElectrique::class)->findAll();
    //     return $this->render('coupure/new.html.twig', [
    //         'controller_name' => 'CoupureController',
    //         'secteurs' => $secteurs,
    //     ]);
    // }

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

        $map_bounds = PolygonType::convertJsonToPolygonWKT($jsoncoords);
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
