<?php

namespace App\Service;

use App\Entity\RegistrationNetPitchFormation;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Repository\GeneralCineNetworkRepository;
use Twig\Environment;

class RegistrationFormationMailerService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private GeneralCineNetworkRepository $cineNetworkRepository;

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        GeneralCineNetworkRepository $cineNetworkRepository,
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->cineNetworkRepository = $cineNetworkRepository;
    }

    public function sendConfirmationToUser(RegistrationNetPitchFormation $registration): void
    {
        $cineNetwork = $this->cineNetworkRepository->findOneBy([]);

        $email = (new Email())
            ->from($cineNetwork?->getEmailCompany())
            ->to($registration->getEmailRegistration())
            ->subject('Confirmation de votre pré-inscription')
            ->html($this->twig->render('net-pitch/confirmation_registration.html.twig', [
                'registration' => $registration,
            ]));

        $this->mailer->send($email);
    }

    public function sendNotificationToAdmin(RegistrationNetPitchFormation $registration): void
    {
        $cineNetwork = $this->cineNetworkRepository->findOneBy([]);
        $personalEmail = $cineNetwork?->getPersonalEmail();

        $email = (new Email())
            ->from($cineNetwork?->getEmailCompany())
            ->to($personalEmail)
            ->subject('Nouvelle pré-inscription reçue')
            ->html($this->twig->render('net-pitch/admin_new_registration.html.twig', [
                'registration' => $registration,
            ]));

        $this->mailer->send($email);
    }
}
