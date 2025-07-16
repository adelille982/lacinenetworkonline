<?php

namespace App\Controller;

use App\Repository\SessionNetPitchFormationRepository;
use App\Repository\GeneralCineNetworkRepository;
use App\Repository\HeaderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SpeakerRepository;

class AboutController extends AbstractController
{
    #[Route('/a-propos', name: 'app_about')]
    public function index(GeneralCineNetworkRepository $generalCineNetworkRepository, SpeakerRepository $speakerRepository, SessionNetPitchFormationRepository $sessionRepository, HeaderRepository $headerRepository): Response
    {
        $otherHeader = $headerRepository->findOneBy([
            'pageTypeHeader' => 'À-propos',
            'draft' => false,
        ]);
        $generalCineNetwork = $generalCineNetworkRepository->findOneBy([]);

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        $sessions = array_filter(
            $sessionRepository->findAll(),
            fn($s) => !$s->isDraft() && !$s->getNetPitchFormation()?->isDraft()
        );

        $futureSessions = array_filter(
            $sessions,
            fn($s) => $s->getStartDateSessionNetPitchFormation() > $now
        );

        $speakers = $speakerRepository->findBy(['typeSpeaker' => 'Entreprise']);

        return $this->render('about/index.html.twig', [
            'controller_name' => 'AboutController',
            'generalCineNetwork' => $generalCineNetwork,
            'speakers' => $speakers,
            'sessions' => $sessions,
            'otherHeader' => $otherHeader,
            'futureSessions' => $futureSessions,
        ]);
    }
}
