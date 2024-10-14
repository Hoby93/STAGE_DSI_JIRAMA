<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendTestEmail(string $to): void
    {
        $email = (new Email())
            ->from('leodubois261@gmail.com')
            ->to($to)
            ->subject('Test Email')
            ->text('Ceci est un email de test envoyé depuis Symfony 6.1.');

        $this->mailer->send($email);
    }

    public function sendPrevisionCoupure(string $rec_email, \App\Entity\Email $mail, $file): void
    {
        // Créer l'email
        $emailMessage = (new Email())
            ->from('noreply@jirama.com')
            ->to($rec_email)
            ->subject($mail->getObjet())
            ->html("<p>{$mail->getContenu()}</p>");

        // Ajouter la pièce jointe
        $emailMessage->attach(
            base64_decode($file->base64), // Décoder le fichier base64
            $file->name,
            $file->type
        );

        // Envoyer l'email
        $this->mailer->send($emailMessage);
    }

    public function sendAccountInitialization(\App\Entity\Email $mail): void
    {
        $email = (new Email())
            ->from('leodubois261@gmail.com')
            ->to($mail->getEmail())
            ->subject($mail->getObjet())
            ->text($mail->getContenu());

        $this->mailer->send($email);
    }
}
