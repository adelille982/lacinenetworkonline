<?php

namespace App\Controller;

use App\Repository\GeneralCineNetworkRepository;
use App\Repository\NetPitchFormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class NavbarController extends AbstractController
{
    public function renderNavbar(
        GeneralCineNetworkRepository $generalCineNetworkRepository,
        NetPitchFormationRepository $formationRepository
    ): Response {
        $generalCineNetwork = $generalCineNetworkRepository->findOneBy([]);

        $firstFormation = $formationRepository->findOneBy(['draft' => false], ['id' => 'ASC']);
        $firstFormationSlug = $firstFormation?->getSlugNetPitchformation();

        return $this->render('general/navbar.html.twig', [
            'generalCineNetwork' => $generalCineNetwork,
            'firstFormationSlug' => $firstFormationSlug,
        ]);
    }
}
