<?php

namespace App\Controller;

use App\Entity\Email;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ConfigController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/parametre/email', name: 'setting_email')]
    public function setEmail(): Response
    {
        $email = new Email();
        $email->init("prevision_coupure");

        return $this->render('config/email.html.twig', [
            'controller_name' => 'ConfigController',
            'email' => $email
        ]);
    }

    #[Route('/parametre/compte', name: 'setting_account')]
    public function setAccount(): Response
    {
        return $this->render('config/compte.html.twig', [
            'controller_name' => 'ConfigController'
        ]);
    }
}
