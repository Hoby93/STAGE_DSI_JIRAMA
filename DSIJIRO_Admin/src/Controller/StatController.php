<?php

namespace App\Controller;

use App\Entity\Filtre;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\StatService;
use App\Entity\StatEffectif;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Util\Util;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_CONSULTANT')]
class StatController extends AbstractController
{
    private StatService $statService;

    public function __construct(StatService $statService)
    {
        $this->statService = $statService;
    }

    #[Route('/stat', name: 'app_stat')]
    public function index(): Response
    {
        return $this->render('stat/index.html.twig', [
            'controller_name' => 'StatController',
        ]);
    }

    #[Route('/effectif-generale', name: 'effectif')]
    public function effectif(): Response
    {
        $statEffectif = new StatEffectif('fokontany', '', 'fokontany', 'asc');
        $statEffectif->init($this->statService);

        return $this->render('stat/effectif.html.twig', [
            'controller_name' => 'StatController',
            'statEffectif' => $statEffectif
        ]);
    }

    #[Route('/effectif-list', name: 'list_effectif', methods: ['POST'])]
    public function listEffectif(Request $request): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent()); // il y a {message: "hello world"} mais comment l extraire

        $filtre = new Filtre($data->page, $data->limit, $data->niv, $data->motclee, $data->orderby, $data->orderinc, '?', '?');

        $effectifs = $this->statService->fetchEffectifByFilter($filtre);

        // Combinaison des données dans une structure de données associative
        $data = [
            'effectifs' => $effectifs,
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/stat-coupure-list', name: 'fiche_stat_coupure_data', methods: ['POST'])]
    public function listStatCoupure(Request $request): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent());

        //$filtre = new Filtre($data->page, $data->limit, $data->niv, $data->motclee, $data->orderby, $data->orderinc, $data->dategroupe, $data->datevalue);
        $filtre = new Filtre(1, 5, $data->niv, $data->motclee, 'niveau', 'asc', $data->dategroupe, $data->datevalue);

        $stats = $this->statService->fetchStatCoupureByNiveau($filtre);

        // Combinaison des données dans une structure de données associative
        $data = [
            'stats' => $stats,
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/stat-coupure-fiche', name: 'list_stat_coupure', methods: ['POST'])]
    public function ficheStatCoupure(Request $request): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent());

        //$filtre = new Filtre(1, 5, $data->niv, $data->motclee, 'asc', $data->dategroupe, $data->datevalue);
        //$filtre = new Filtre(1, 5, 'secteur', 'S1', 'niveau', 'asc', 'mois', '2024-08');
        $filtre = new Filtre($data->page, $data->limit, $data->niv, $data->motclee, $data->orderby, $data->orderinc, $data->dategroupe, $data->datevalue);

        $stats = $this->statService->fetchStatCoupureByFilter($filtre);

        // Combinaison des données dans une structure de données associative
        $data = [
            'stats' => $stats,
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/statistique-coupure', name: 'stat_coupure')]
    public function statCoupure(): Response
    {
        return $this->render('stat/coupure.stat.html.twig', [
            'controller_name' => 'StatController',
        ]);
    }

    

    #[Route('/statistique-coupure/fiche/{gpniv}/{niv}', name: 'fiche_stat_coupure')]
    public function DetailStatCoupure(?string $gpniv = null, ?string $niv = null): Response
    {
        return $this->render('stat/coupure.stat.fiche.html.twig', [
            'controller_name' => 'StatController',
            'gpniv' => $gpniv,
            'niv' => $niv
        ]);
    }
}
