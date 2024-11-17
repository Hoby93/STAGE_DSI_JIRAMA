<?php

namespace App\Controller;

use App\Doctrine\PolygonType;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class InfraController extends AbstractController
{
    #[Route('/liste-infrastructure', name: 'list_infra')]
    public function list_infra(EntityManagerInterface $entityManager): Response
    {
        $infras = $entityManager->getRepository(Infrastructure::class)->findAll();

        return $this->render('infra/list.html.twig', [
            'controller_name' => 'InfraController',
            'infras' => $infras,
        ]);
    }

    #[Route('/carte-infrastructure', name: 'map_infra')]
    public function map_infra(EntityManagerInterface $entityManager): Response
    {
        return $this->render('infra/map.html.twig', [
            'controller_name' => 'InfraController'
        ]);
    }

    #[Route('/ajouter-site', name: 'new_infra')]
    public function new_infra(EntityManagerInterface $entityManager): Response
    { 
        $tinfras = $entityManager->getRepository(TypeInfrastructure::class)->findAll();
        return $this->render('infra/new.html.twig', [
            'controller_name' => 'InfraController',
            'typeinfras' => $tinfras,
        ]);
    }

    #[Route('/modifier-site/{id}', name: 'update_infra')]
    public function update_infra(EntityManagerInterface $em, ?string $id = null): Response
    { 
        $tinfras = $em->getRepository(TypeInfrastructure::class)->findAll();
        $infra = $em->getRepository(Infrastructure::class)->findById($id);

        // var_dump($id);
        return $this->render('infra/update.html.twig', [
            'controller_name' => 'InfraController',
            'typeinfras' => $tinfras,
            'infra' => $infra
        ]);
    }

    #[Route('/inserer-site', name: 'new_infra_do', methods: ['POST'])]
    public function createInfrastructure(Request $request, EntityManagerInterface $em, TypeInfrastructureRepository $typeInfrastructureRepository)
    {
        $typeInfraId = $request->request->get('typeInfra');
        $typeInfra = $typeInfrastructureRepository->find($typeInfraId);
        
        // Convertir les coordonnées en tableau [latitude, longitude]
        $coord = explode(',', str_replace(['LatLng(', ')'], '', $request->request->get('coord')));

        $infra = new Infrastructure();
        $infra->setRefInfra($request->request->get('refInfra'))
                       ->setTypeInfra($typeInfraId)
                       ->setFktId($request->request->get('fktId'))
                       ->setLibelle($request->request->get('libelle'))
                       ->setAdresse($request->request->get('adresse'))
                       ->setContact($request->request->get('contact'))
                       ->setDescr($request->request->get('descr'))
                       ->setCoord(['latitude' => floatval($coord[0]), 'longitude' => floatval($coord[1])])
                       ->setTypeInfrastructure($typeInfra);

        // Obtenez et inspectez les horaires
        $donneesFormulaire = $request->request->all();
        $horaire = $donneesFormulaire['horaire'] ?? null;

        $horaireString = "lun:{$horaire['lun']['ouverture']}-{$horaire['lun']['fermeture']};" .
                         "mar:{$horaire['mar']['ouverture']}-{$horaire['mar']['fermeture']};" .
                         "mer:{$horaire['mer']['ouverture']}-{$horaire['mer']['fermeture']};" .
                         "jeu:{$horaire['jeu']['ouverture']}-{$horaire['jeu']['fermeture']};" .
                         "ven:{$horaire['ven']['ouverture']}-{$horaire['ven']['fermeture']};";
        $infra->setHoraire($horaireString);

        $em->getRepository(Infrastructure::class)->save($infra); // Inserer le site

        // var_dump($infra);

        // return $this->redirectToRoute('new_infra'); // Modifier cette route selon vos besoins
        $tinfras = $em->getRepository(TypeInfrastructure::class)->findAll();
        return $this->render('infra/new.html.twig', [
            'controller_name' => 'InfraController',
            'typeinfras' => $tinfras,
        ]);
    }

    #[Route('/modifier-site-app', name: 'update_infra_do', methods: ['POST'])]
    public function updateInfrastructure(Request $request, EntityManagerInterface $em, TypeInfrastructureRepository $typeInfrastructureRepository)
    {
        $typeInfraId = $request->request->get('typeInfra');
        $typeInfra = $typeInfrastructureRepository->find($typeInfraId);
        
        // Convertir les coordonnées en tableau [latitude, longitude]
        $coord = explode(',', str_replace(['LatLng(', ')'], '', $request->request->get('coord')));

        $infra = new Infrastructure();
        $infra->setRefInfra($request->request->get('refInfra'))
                       ->setId($request->request->get('idinfra'))
                       ->setTypeInfra($typeInfraId)
                       ->setFktId($request->request->get('fktId'))
                       ->setLibelle($request->request->get('libelle'))
                       ->setAdresse($request->request->get('adresse'))
                       ->setContact($request->request->get('contact'))
                       ->setDescr($request->request->get('descr'))
                       ->setCoord(['latitude' => floatval($coord[0]), 'longitude' => floatval($coord[1])])
                       ->setTypeInfrastructure($typeInfra);

        // Obtenez et inspectez les horaires
        $donneesFormulaire = $request->request->all();
        $horaire = $donneesFormulaire['horaire'] ?? null;

        $horaireString = "lun:{$horaire['lun']['ouverture']}-{$horaire['lun']['fermeture']};" .
                         "mar:{$horaire['mar']['ouverture']}-{$horaire['mar']['fermeture']};" .
                         "mer:{$horaire['mer']['ouverture']}-{$horaire['mer']['fermeture']};" .
                         "jeu:{$horaire['jeu']['ouverture']}-{$horaire['jeu']['fermeture']};" .
                         "ven:{$horaire['ven']['ouverture']}-{$horaire['ven']['fermeture']};";
        $infra->setHoraire($horaireString);

        $em->getRepository(Infrastructure::class)->update($infra); // Modifier le site

        // var_dump($infra);

        return $this->redirectToRoute('list_infra'); // Modifier cette route selon vos besoins
    }

    #[Route('/infrastructure/liste/recherche', name: 'search_infra_on_liste', methods: ['POST'])]
    public function searchZone(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent()); // il y a {message: "hello world"} mais comment l extraire

        $filtre = new Filtre($data->page, $data->limit, null, $data->motclee, null, null, '?', '?');
        $infras = $em->getRepository(Infrastructure::class)->findByMultiFilter($filtre);
        
        // Combinaison des données dans une structure de données associative
        $data = [
            'infras' => $infras,
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/infra/visible', name: 'visible_infra', methods: ['POST'])]
    public function getVisibleinfra(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent()); // il y a {message: "hello world"} mais comment l extraire
        $jsoncoords = json_encode($data->coord);

        $map_bounds = PolygonType::convertJsonToPolygonWKT($jsoncoords);

        $infras = $em->getRepository(Infrastructure::class)->getAllIntersect($map_bounds);
        //$infras = $em->getRepository(Infrastructure::class)->findAll();

        // Combinaison des données dans une structure de données associative
        $data = [
            'infras' => $infras,
            'bounds' => $map_bounds
        ];

        // echo(new JsonResponse($infras));

        return new JsonResponse(Util::toJson($data));
        // return new JsonResponse("Hello world");
    }
}
