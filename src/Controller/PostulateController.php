<?php

namespace App\Controller;

use App\Entity\Postulate;
use App\Repository\GeneralCineNetworkRepository;
use App\Repository\HeaderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostulateController extends AbstractController
{
    #[Route('/postuler', name: 'app_postulate', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, GeneralCineNetworkRepository $generalCineNetworkRepository, HeaderRepository $headerRepository): Response
    {

        $generalCineNetwork = $generalCineNetworkRepository->findOneBy([]);

        $otherHeader = $headerRepository->findOneBy([
            'pageTypeHeader' => 'Postuler',
            'draft' => false,
        ]);

        if ($request->isMethod('POST')) {

            if (!$request->request->get('terms-postulate')) {
                $this->addFlash('error_postulate', 'Vous devez accepter les conditions d\'utilisation.');
                return $this->redirectToRoute('app_postulate');
            }

            $postulate = new Postulate();
            $postulate->setFirstname(trim($request->request->get('first-name-postulate')));
            $postulate->setLastname(trim($request->request->get('last-name-postulate')));
            $postulate->setEmail(trim($request->request->get('email-postulate')));
            $postulate->setTelephone(trim($request->request->get('phone-postulate')));
            $postulate->setProfessionalExperience(trim($request->request->get('short-description-postulate')));

            $postulate->setConditionValidated(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

            $cvFile = $request->files->get('cv-upload-postulate');
            if (!$cvFile) {
                $this->addFlash('error_postulate', 'Veuillez joindre un fichier CV.');
                return $this->redirectToRoute('app_postulate', ['_fragment' => 'postuler-chez-cine-network']);
            }

            $extension = $cvFile->guessClientExtension() ?? 'pdf';
            $safeLastName = preg_replace('/[^a-z0-9]/i', '-', strtolower(transliterator_transliterate('Any-Latin; Latin-ASCII', $postulate->getLastname())));
            $safeFirstName = preg_replace('/[^a-z0-9]/i', '-', strtolower(transliterator_transliterate('Any-Latin; Latin-ASCII', $postulate->getFirstname())));
            $baseFilename = 'cv-' . $safeLastName . '-' . $safeFirstName;
            $uploadDir = $this->getParameter('cv_directory_entreprise');
            $cvFilename = $baseFilename . '.' . $extension;

            $i = 1;
            while (file_exists($uploadDir . '/' . $cvFilename)) {
                $cvFilename = $baseFilename . '-' . $i . '.' . $extension;
                $i++;
            }

            try {
                $cvFile->move($uploadDir, $cvFilename);
                $postulate->setCuriculum('cv/cv-cine-network/' . $cvFilename);
            } catch (FileException $e) {
                $this->addFlash('error_postulate', 'Erreur lors du téléchargement du CV.');
                return $this->redirectToRoute('app_postulate', ['_fragment' => 'postuler-chez-cine-network']);
            }

            $em->persist($postulate);
            $em->flush();

            $this->addFlash('success_postulate', 'Votre candidature a bien été envoyée.');
            return $this->redirectToRoute('app_postulate', ['_fragment' => 'postuler-chez-cine-network']);
        }

        return $this->render('postulate/index.html.twig', [
            'controller_name' => 'PostulateController',
            'generalCineNetwork' => $generalCineNetwork,
            'otherHeader' => $otherHeader,
        ]);
    }
}
