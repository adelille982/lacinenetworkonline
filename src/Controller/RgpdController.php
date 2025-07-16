<?php

namespace App\Controller;

use App\Repository\GeneralCineNetworkRepository;
use App\Repository\HeaderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RgpdController extends AbstractController
{
    #[Route('/rgpd', name: 'app_rgpd')]
    public function index(GeneralCineNetworkRepository $generalCineNetworkRepository, HeaderRepository $headerRepository): Response
    {
        $otherHeader = $headerRepository->findOneBy([
            'pageTypeHeader' => 'RGPD',
            'draft' => false,
        ]);
        $generalCineNetwork = $generalCineNetworkRepository->findOneBy([]);

        return $this->render('rgpd/index.html.twig', [
            'controller_name' => 'RgpdController',
            'generalCineNetwork' => $generalCineNetwork,
            'otherHeader' => $otherHeader,
        ]);
    }
}
