<?php

namespace App\Controller;

use App\Repository\GeneralCineNetworkRepository;
use App\Repository\NetPitchFormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FooterController extends AbstractController
{
    public function renderFooter(NetPitchFormationRepository $formationRepository, GeneralCineNetworkRepository $generalCineNetworkRepository): Response
    {

        $generalCineNetwork = $generalCineNetworkRepository->findOneBy([]);
        $firstFormation = $formationRepository->findOneBy(['draft' => false], ['id' => 'ASC']);
        $firstFormationSlug = $firstFormation?->getSlugNetPitchformation();

        return $this->render('general/footer.html.twig', [
            'generalCineNetwork' => $generalCineNetwork,
            'firstFormationSlug' => $firstFormationSlug,
        ]);
    }
}
