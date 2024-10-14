<?php

namespace App\Controller;

use App\Entity\Employe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginController extends AbstractController
{
    #[Route('/connexion', name: 'to_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Obtenez l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier email entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/connexion/verifier', name: 'login', methods: ['POST'])]
    public function checkLog(): Response
    {
        // Symfony gère automatiquement la logique de connexion
        // Vous n'avez pas besoin d'implémenter cette méthode vous-même
        // Elle est utilisée pour la validation du formulaire
        return new Response('', Response::HTTP_OK);
    }

    #[Route('/deconnexion', name: 'logout')]
    public function logout(): void
    {
        // Symfony gère automatiquement la déconnexion pour vous
    }

    #[Route('/access-refusee', name: 'access_denied')]
    public function accessDenied(): Response
    {
        return $this->render('login/page403.html.twig');
    }
}
