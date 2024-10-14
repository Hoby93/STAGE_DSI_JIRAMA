<?php

namespace App\Controller;

use App\Entity\Contact;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Infrastructure;
use App\Entity\ZoneElectrique;
use App\Entity\Filtre;
use App\Entity\PostElectrique;
use App\Entity\SecteurElectrique;
use App\Service\EmailService;
use App\Service\ZoneCoupeeService;
use App\Service\StatService;
use App\Util\Util;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TestController extends AbstractController
{
    private ZoneCoupeeService $zoneCoupeeService;

    public function __construct(ZoneCoupeeService $zoneCoupeeService)
    {
        $this->zoneCoupeeService = $zoneCoupeeService;
    }

    #[Route('/test', name: 'app_test')]
    public function index(EntityManagerInterface $em): Response
    {
        $infras = $em->getRepository(Infrastructure::class)->findAll();
        $zonesCoupee = $this->zoneCoupeeService->fetchZoneCoupeeByDate('2024-07-30');
        $zones = $em->getRepository(ZoneElectrique::class)->findAll();

        $zonesCoupee = $this->zoneCoupeeService->fetchZoneCoupeeOnBoundsByDate('2024-07-30 00:00:00', '2024-09-19 23:00:00');

        // $infras = $em->getRepository(infra::class)->getAllIntersect($infra);
        $views = $em->getRepository(Infrastructure::class)->findNearestOfUserPosition("-18.97448843460287", "47.537528675433194");
        dump($views);

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
            'infras' => $infras,
        ]);
    }

    #[Route('/coupure/visible2', name: 'visible_coupure2', methods: ['POST'])]
    public function getVisibleZoneCoupee(EntityManagerInterface $em): JsonResponse
    {
        $filtre = new Filtre(1, 5, null, '', null, null, '?', '?');
        $zones = $em->getRepository(ZoneElectrique::class)->findByMultiFilter($filtre, '', '');

        // Combinaison des données dans une structure de données associative
        $data = [
            'zonesCoupee' => $zones
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
        // return new JsonResponse("Hello world");
    }
}
