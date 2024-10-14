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
use App\Service\ParametrageService;
use App\Service\ZoneCoupeeService;
use App\Service\StatService;
use App\Util\Util;

class TestController extends AbstractController
{
    private ZoneCoupeeService $zoneCoupeeService;
    private StatService $statService;
    private EmailService $emailService;
    private ParametrageService $parametrageService;

    public function __construct(ZoneCoupeeService $zoneCoupeeService, StatService $statService, EmailService $emailService, ParametrageService $parametrageService)
    {
        $this->zoneCoupeeService = $zoneCoupeeService;
        $this->statService = $statService;
        $this->emailService = $emailService;
        $this->parametrageService = $parametrageService;
    }

    #[Route('/test', name: 'app_test')]
    public function index(EntityManagerInterface $em): Response
    {
        $infras = $em->getRepository(Infrastructure::class)->findAll();
        $zonesCoupee = $this->zoneCoupeeService->fetchZoneCoupeeByDate('2024-07-30');
        $zones = $em->getRepository(ZoneElectrique::class)->findAll();

        $filtre = new Filtre(1, 5, 'region', '', 'region', 'asc', 'mois', '2024-08');
        $effectifs = $this->statService->fetchEffectifByFilter($filtre);

        $contacts = $em->getRepository(Contact::class)->findAll();

        $filtre = new Filtre(1, 5, null, '', null, null, '?', '?');
        $zones = $em->getRepository(ZoneElectrique::class)->findByMultiFilter($filtre, '', '');

        $infras2 = $em->getRepository(Infrastructure::class)->findByMultiFilter($filtre);

        $secteurs2 = $em->getRepository(SecteurElectrique::class)->findByMultiFilter($filtre);
        $postes2 = $em->getRepository(PostElectrique::class)->findByMultiFilter($filtre);

        $filtre = new Filtre(1, 5, 'zone', '', ' niveau', 'asc', '?', '?');
        $filtre->setDateInterval('', '');
        
        $stats = $this->statService->fetchStatCoupureByFilter($filtre);

        // $listefkt = $this->parametrageService->fetchFokontany('');

        // $zonesCoupee = $this->zoneCoupeeService->fetchZoneCoupeeByFilter($filtre);

        dump($stats);

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
            'infras' => $infras,
        ]);
    }

    #[Route('/test/email', name: 'app_send_email')]
    public function sendEmail(): Response
    {
        $this->emailService->sendTestEmail('rkthobiniaina@gmail.com');

        return new Response('Email envoyé avec succès!');
    }
}
