<?php

namespace App\Controller;

use App\Entity\Email;
use App\Entity\Employe;
use App\Entity\Filtre;
use App\Service\EmailService;
use App\Util\Util;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    #[Route('/utilisateur', name: 'list_user')]
    public function index(): Response
    {
        return $this->render('user/user.list.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/utilisateur/recherche', name: 'search_user_on_liste', methods: ['POST'])]
    public function searchUser(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Décoder le contenu JSON du requette
        $data = json_decode($request->getContent());

        $filtre = new Filtre($data->page, $data->limit, null, $data->motclee, null, null, '?', '?');
        $employes = $em->getRepository(Employe::class)->findByMultiFilter($filtre);
        
        // Combinaison des données dans une structure de données associative
        $data = [
            'employes' => $employes
        ];

        // echo(new JsonResponse($zonesCoupee));

        return new JsonResponse(Util::toJson($data));
    }

    #[Route('/utilisateur/nouveau', name: 'new_user')]
    public function newUser(): Response
    {
        return $this->render('user/user.new.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/modifier-compte', name: 'update_account', methods: ['POST'])]
    public function updateAccount(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder)
    {
        $data = json_decode($request->getContent());

        /** @var Employe $curr_user */
        $curr_user = $this->getUser();

        $mdp = $curr_user->getMdp();
        if($data->setPwd) {
            if (!$passwordEncoder->isPasswordValid($curr_user, $data->mdp)) {
                return new JsonResponse(Util::toJson(['status' => 0, 'message' => 'Mot de passe incorrect']));
            }
            $mdp = $passwordEncoder->hashPassword($curr_user, $data->newPwd);
        }
        $curr_user->init($data->nom, $data->prenom, $data->email, $mdp, $curr_user->getProfil(), $curr_user->getPosition());
        
        $em->persist($curr_user); $em->flush(); // Modifier l'utilisateur
        return new JsonResponse(Util::toJson([
            'status' => 1,
            'message' => 'Le mot de passe correct',
            'user' => $curr_user
        ]));
    }

    #[Route('/inserer-utilisateur', name: 'insert_user', methods: ['POST'])]
    public function addUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder): JsonResponse
    {
        // Décoder le contenu JSON du requette 
        $data = json_decode($request->getContent()); // il y a {message: "hello world"} mais comment l extraire
        
        // $emails = $data->email;
        // $file = $data->file;

        $user = new Employe();
        $pwd = sprintf('%08d', mt_rand()); // Generer une chaine de 8 cts composé de chiffre
        $pwdh= $passwordEncoder->hashPassword($user, $pwd); // mdp hachee

        $user->init($data->nom, $data->prenom, $data->email, $pwdh, $data->role, $data->position);

        $res = ['status' => 1, 'message' => 'insertion !ok', 'user' => $user];

        try {
            $em->persist($user); $em->flush(); // Inserer l'utilisateur
            $mail = new Email();
            $mail->init("nouveau_compte");
            $mail->setEmail($data->email);
            $mail->setContenuOption("<nom>", $data->prenom);
            $mail->setContenuOption("<role>", $data->role);
            $mail->setContenuOption("<motdepasse>", $pwd);

            $this->emailService->sendAccountInitialization($mail);
        } catch (\Exception $e) {
            // Réponse d'erreur
            $res = ['status' => 0, 'message' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage()];
        }

        return new JsonResponse(Util::toJson($res));
    }
}
