<?php

namespace App\Controller;

use App\Entity\Email;
use App\Service\EmailService;
use App\Util\Util;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('ROLE_OFFICER')]
class CommunicationController extends AbstractController
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    #[Route('/communication/envoye-email', name: 'to_send_email')]
    public function toSendEmail(): Response
    {
        $email = new Email();
        $email->init("prevision_coupure");

        return $this->render('communication/email.html.twig', [
            'controller_name' => 'CommunicationController',
            'email' => $email
        ]);
    }

    #[Route('/envoye-email', name: 'send_email', methods: ['POST'])]
    public function sendEmail(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Décoder le contenu JSON du requette 
        $data = json_decode($request->getContent()); // il y a {message: "hello world"} mais comment l extraire
        
        $emails = $data->email;

        $email = new Email();
        $email->init("prevision_coupure");

        $res = ['status' => 1, 'message' => '!ok'];

        try {
            $this->emailService->sendPrevisionCoupure('rkthobiniaina@gmail.com', $email, $data->file);
        } catch (\Exception $e) {
            // Réponse d'erreur
            $res = ['status' => 0, 'message' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage()];
        }

        return new JsonResponse(Util::toJson($res));
    }
}
