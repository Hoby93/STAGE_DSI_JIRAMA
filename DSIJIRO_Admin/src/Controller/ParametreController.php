<?php

namespace App\Controller;

use App\Util\Util;
use App\Entity\Lieu;
use App\Entity\SecteurElectrique;
use App\Entity\ZoneElectrique;
use App\Entity\PostElectrique;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\SecteurElectriqueRepository;
use App\Repository\PostElectriqueRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use App\Doctrine\PolygonType;
use App\Entity\Contact;
use App\Entity\Filtre;
use App\Service\ParametrageService;
use PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_OFFICER')]
class ParametreController extends AbstractController
{
    private ParametrageService $parametrageService;

    public function __construct(ParametrageService $parametrageService)
    {
        $this->parametrageService = $parametrageService;
    }

    #[Route('/parametre', name: 'app_parametre')]
    public function index(): Response
    {
        return $this->render('parametre/index.html.twig', [
            'controller_name' => 'ParametreController',
        ]);
    }

    #[Route('territoire/secteur', name: 'search_secteur', methods: ['GET'])]
    public function searchRegion(Request $request): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent());

        $secteurs = $this->parametrageService->fetchSecteur($data->motclee);

        // Combinaison des données dans une structure de données associative
        $data = [
            'secteurs' => $secteurs,
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('territoire/region', name: 'fetch_region', methods: ['GET'])]
    public function getRegion(Request $request): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent());

        $regions = $this->parametrageService->fetchRegion();

        // Combinaison des données dans une structure de données associative
        $data = [
            'regions' => $regions
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/territoire/fokontany/recherche', name: 'search_fokontany', methods: ['POST'])]
    public function searchFokontany(Request $request): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent());

        $listefkt = $this->parametrageService->fetchFokontany($data->motclee);


        // Combinaison des données dans une structure de données associative
        $data = [
            'listefkt' => $listefkt,
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('parametrage/secteur/recherche-par-nom', name: 'search_secteur', methods: ['POST'])]
    public function searchSecteur(Request $request): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent());

        $secteurs = $this->parametrageService->fetchSecteur($data->motclee);

        // Combinaison des données dans une structure de données associative
        $data = [
            'secteurs' => $secteurs,
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/liste-contact', name: 'list_contact', methods: ['GET'])]
    public function getContact(EntityManagerInterface $em): JsonResponse
    {
        $contacts = $em->getRepository(Contact::class)->findAll();

        // Combinaison des données dans une structure de données associative
        $data = [
            'contacts' => $contacts
        ];

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/parametrage/secteur/recherche', name: 'search_secteur_on_liste', methods: ['POST'])]
    public function searchZone(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent());

        $filtre = new Filtre($data->page, $data->limit, null, $data->motclee, null, null, '?', '?');
        $secteurs = $em->getRepository(SecteurElectrique::class)->findByMultiFilter($filtre);
        
        // Combinaison des données dans une structure de données associative
        $data = [
            'secteurs' => $secteurs
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/parametrage/poste/recherche', name: 'search_poste_on_liste', methods: ['POST'])]
    public function searchPoste(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent());

        $filtre = new Filtre($data->page, $data->limit, null, $data->motclee, null, null, '?', '?');
        $postes = $em->getRepository(PostElectrique::class)->findByMultiFilter($filtre);
        
        // Combinaison des données dans une structure de données associative
        $data = [
            'postes' => $postes
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/ajout-contact', name: 'new_contact', methods: ['POST'])]
    public function insertContact(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Décoder le contenu JSON du requette 
        $data = json_decode($request->getContent());

        $contact = new Contact();
        $contact->init($data->nom, $data->prenom, $data->fonction, $data->email);

        $em->persist($contact); $em->flush(); // Inserer le contact

        $data = [
            'status' => 1,
            'message' => 'Ok'
        ];

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/liste-zone', name: 'list_zone')]
    public function listZone(Request $request, EntityManagerInterface $entityManager): Response
    {
        $zones = $entityManager->getRepository(ZoneElectrique::class)->findAll();
        $message = $request->query->get('message');

        return $this->render('parametre/zone.list.html.twig', [
            'zones' => $zones,
            'message' => $message
        ]);
    }

    #[Route('/modifier-zone/{id}', name: 'to_update_zone')]
    public function to_update_zone(EntityManagerInterface $em, ?string $id = null): Response
    { 
        $zone = $em->getRepository(ZoneElectrique::class)->findById($id);

        // var_dump($zone);
        return $this->render('parametre/zone.update.html.twig', [
            'zone' => $zone
        ]);
    }

    #[Route('/nouvelle-zone', name: 'new_zone')]
    public function newZone(Request $request): Response
    {
        return $this->render('parametre/zone.new.html.twig', [
            'zone' => null
        ]);
    }

    #[Route('/information-zone-geographique', name: 'get_zone_property', methods: ['POST'])]
    public function getZoneInfo(Request $request): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent());

        $bounds = PolygonType::findMinMaxCoordinates($data->coords);
        
        $spec = ZoneElectrique::getSpecificities($bounds);
        $lieux = ZoneElectrique::getPlaceNames($bounds);

        // Combinaison des données dans une structure de données associative
        $data = [
            'specificite' => $spec,
            'lieux' => $lieux,
        ];

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/insert-zone', name: 'insert_zone')]
    public function insertZone(Request $request, EntityManagerInterface $em)
    {
        $zone = new ZoneElectrique();
        $lieux = $request->request->get('lieux');
        $ref_secteur = $request->request->get('refsecteur');

        $zone->init(
            0, // id_zone
            $ref_secteur,
            $request->request->get('refZone'), // ref_zone
            $request->request->get('specificite'), // specificite
            $lieux, // lieux
            $request->request->get('postescoupee'), // postes electriques
            1, // degree
            null // Coord
        );

        $zone->initDegree();

        // var_dump($zone);

        $em->getRepository(SecteurElectrique::class)->insertMissingSecteur($ref_secteur); // Inserer si secteur inexistant

        $id_secteur = $em->getRepository(SecteurElectrique::class)->findIdByRefSecteur($ref_secteur);
        //echo("id_zone: ".$id_zone."/id_secteur: ".$id_secteur);

        $coords = PolygonType::convertLatLngToPolygonWKT($request->request->get('coords'));

        $em->getRepository(ZoneElectrique::class)->save($zone, $coords); // Inserer la zone
        $id_zone = $em->getRepository(ZoneElectrique::class)->getLastZoneElectriqueId();

        $zone->setId($id_zone);

        $places = explode('-', $lieux);

        $em->getRepository(PostElectrique::class)->insertMissingPosts($request->request->get('postescoupee'), $id_secteur);
        $em->getRepository(ZoneElectrique::class)->saveZonePost($request->request->get('postescoupee'), $id_zone); // Inserer la zone_post
        $em->getRepository(Lieu::class)->insertMissingPlace($places, $id_secteur); // Inserer les nouveaux lieu
        $em->getRepository(ZoneElectrique::class)->saveZonePlace($places, $id_zone); // Inserer la zone_place

        return $this->render('parametre/zone.new.html.twig', [
            'message' => 'Ok!',
            'zone' => Util::toJson($zone)
        ]);
    }

    #[Route('/modifier-zone-do', name: 'update_zone_do')]
    public function updateZone(Request $request, EntityManagerInterface $em)
    {
        $zone = new ZoneElectrique();
        $lieux = $request->request->get('lieux');
        $ref_secteur = $request->request->get('refsecteur');

        $zone->init(
            $request->request->get('idZone'), // id_zone
            $ref_secteur,
            $request->request->get('refZone'), // ref_zone
            $request->request->get('specificite'), // specificite
            $lieux, // lieux
            $request->request->get('postescoupee'), // postes electriques
            1, // degree
            null // Coord
        );

        $zone->initDegree();

        // var_dump($zone);

        // $em->getRepository(SecteurElectrique::class)->insertMissingSecteur($ref_secteur); // Inserer si secteur inexistant

        $id_secteur = $em->getRepository(SecteurElectrique::class)->findIdByRefSecteur($ref_secteur);
        //echo("id_zone: ".$id_zone."/id_secteur: ".$id_secteur);

        $coords = PolygonType::convertLatLngToPolygonWKT($request->request->get('coords'));

        $em->getRepository(ZoneElectrique::class)->update($zone, $coords); // Modifier la zone
        $id_zone = $request->request->get('idZone');
        $places = explode('-', $lieux);

        $em->getRepository(ZoneElectrique::class)->resetZonePostAndPlace($id_zone); // Initialiser les postes et lieux associee au zone

        $em->getRepository(PostElectrique::class)->insertMissingPosts($request->request->get('postescoupee'), $id_secteur);
        $em->getRepository(ZoneElectrique::class)->saveZonePost($request->request->get('postescoupee'), $id_zone); // Inserer la zone_post
        $em->getRepository(Lieu::class)->insertMissingPlace($places, $id_secteur); // Inserer les nouveaux lieu
        $em->getRepository(ZoneElectrique::class)->saveZonePlace($places, $id_zone); // Inserer la zone_place

        return $this->redirectToRoute('to_update_zone', ['id' => $id_zone]);
    }

    #[Route('/liste-secteur', name: 'list_secteur')]
    public function listSecteur(Request $request): Response
    {
        return $this->render('parametre/secteur.list.html.twig', [
            'message' => $request->query->get('message')
        ]);
    }

    #[Route('/insert-secteur', name: 'new_secteur_do', methods: ['POST'])]
    public function insertSecteur(Request $request, EntityManagerInterface $em): Response
    {
        $ref = $request->request->get('ref');
        $idregion = $request->request->get('idregion');

        $new_secteur = new SecteurElectrique();
        $new_secteur->setRefSecteur($ref)
                    ->setRegionId($idregion);

        $message = null;
        try {
            $em->getRepository(SecteurElectrique::class)->save($new_secteur); // Inserer le nouveau secteur
        } catch (UniqueConstraintViolationException $e) {
            // Gérer l'exception de contrainte d'unicité
            $message = 'Ref Secteur déjà existant';
        } catch (ORMException $e) {
            // Gérer les autres exceptions ORM
            $message = 'Échec de l\'opération';
        }

        return $this->redirectToRoute('list_secteur', ['message' => $message]);
    }

    #[Route('/modifier-secteur', name: 'update_secteur_do', methods: ['POST'])]
    public function updateSecteur(Request $request, EntityManagerInterface $em): Response
    {
        $id = $request->request->get('idsecteur');
        $ref = $request->request->get('ref');
        $idregion = $request->request->get('idregion');

        $new_secteur = new SecteurElectrique();
        $new_secteur->setId($id)
                    ->setRefSecteur($ref)
                    ->setRegionId($idregion);

        $message = null;
        try {
            $em->getRepository(SecteurElectrique::class)->update($new_secteur); // Inserer le nouveau secteur
        } catch (UniqueConstraintViolationException $e) {
            // Gérer l'exception de contrainte d'unicité
            $message = 'Ref Secteur déjà existant';
        } catch (ORMException $e) {
            // Gérer les autres exceptions ORM
            $message = 'Échec de l\'opération';
        }

        return $this->redirectToRoute('list_secteur', ['message' => $message]);
    }

    #[Route('/liste-poste-electrique', name: 'list_post')]
    public function listpost(Request $request): Response
    {
        return $this->render('parametre/post.list.html.twig', [
            'message' => $request->query->get('message')
        ]);
    }

    #[Route('/insert-post', name: 'new_post_do', methods: ['POST'])]
    public function insertPost(Request $request, EntityManagerInterface $em): Response
    {
        $ref = $request->request->get('ref');
        $secteur_id = $request->request->get('idsecteur');

        $new_post = new PostElectrique();
        $new_post->setRefPost($ref)
                 ->setSecteurId($secteur_id);

        $message = null;
        try {
            $em->getRepository(PostElectrique::class)->save($new_post); // Inserer le nouveau poste
        } catch (UniqueConstraintViolationException $e) {
            // Gérer l'exception de contrainte d'unicité
            $message = 'Ref post déjà existant';
        } catch (ORMException $e) {
            // Gérer les autres exceptions ORM
            $message = 'Échec de l\'opération';
        }

        return $this->redirectToRoute('list_post', ['message' => $message]);
    }

    #[Route('/update-post', name: 'update_post_do', methods: ['POST'])]
    public function updatePost(Request $request, EntityManagerInterface $em): Response
    {
        $id = $request->request->get('idpost');
        $ref = $request->request->get('ref');
        $secteur_id = $request->request->get('idsecteur');

        $poste = new PostElectrique();
        $poste->setId($id)
              ->setRefPost($ref)
              ->setSecteurId($secteur_id);
        $message = null;
        try {
            $em->getRepository(PostElectrique::class)->update($poste);
        } catch (UniqueConstraintViolationException $e) {
            // Gérer l'exception de contrainte d'unicité
            $message = 'Ref poste déjà existant';
        } catch (ORMException $e) {
            // Gérer les autres exceptions ORM
            $message = 'Échec de l\'opération';
        }

        return $this->redirectToRoute('list_post', ['message' => $message]);
    }
}
