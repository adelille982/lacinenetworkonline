<?php

namespace App\Controller;

use App\Entity\RegistrationNetPitchFormation;
use App\Repository\SessionNetPitchFormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Repository\GeneralCineNetworkRepository;
use App\Repository\CommentaryRepository;
use App\Entity\Commentary;
use App\Repository\HeaderRepository;
use DateTimeImmutable;
use App\Repository\NetPitchFormationRepository;
use App\Service\RegistrationFormationMailerService;

class NetPitchController extends AbstractController
{
    #[Route('/formations/{slug}', name: 'app_net-pitch', requirements: ['slug' => '.+'], defaults: ['slug' => null])]
    public function show(
        ?string $slug,
        NetPitchFormationRepository $formationRepository,
        SessionNetPitchFormationRepository $sessionRepository,
        Request $request,
        EntityManagerInterface $em,
        GeneralCineNetworkRepository $generalCineNetworkRepository,
        CommentaryRepository $commentaryRepository,
        RegistrationFormationMailerService $mailerService,
        HeaderRepository $headerRepository,
    ): Response {
        if (!$slug && !$request->isMethod('POST')) {
            return $this->redirectToRoute('app_index');
        }

        if ($request->isMethod('POST')) {
            $slug = $request->request->get('slug');
        }

        if (!$slug) {
            return $this->redirectToRoute('app_index');
        }

        $formation = $formationRepository->findOneBy(['slugNetPitchformation' => $slug]);

        if (!$formation || $formation->isDraft()) {
            throw $this->createNotFoundException('Formation non trouvée');
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        $sessions = array_filter(
            $formation->getSessionNetPitchFormations()->toArray(),
            fn($s) => !$s->isDraft()
        );

        $futureSessions = array_filter(
            $sessions,
            fn($s) => $s->getStartDateSessionNetPitchFormation() > $now
        );

        $otherHeader = $headerRepository->findOneBy([
            'pageTypeHeader' => 'Formation',
            'draft' => false,
        ]);

        $generalCineNetwork = $generalCineNetworkRepository->findAll()[0];

        $formations = array_filter(
            $formationRepository->findBy([], ['id' => 'ASC']),
            fn($f) => !$f->isDraft()
        );

        if (empty($formations)) {
            return $this->redirectToRoute('app_index');
        }
        $firstFormation = $formations[0];
        $firstFormationSlug = $firstFormation->getSlugNetPitchformation();

        $currentIndex = array_search($formation, $formations, true);

        $previousFormation = $formations[($currentIndex - 1 + count($formations)) % count($formations)];
        $nextFormation = $formations[($currentIndex + 1) % count($formations)];

        $sessionSpeakersMap = [];

        foreach ($formation->getSessionNetPitchFormations() as $session) {
            if (!$session->isDraft()) {
                $sessionId = $session->getId();
                $sessionSpeakersMap[$sessionId] = [];

                foreach ($session->getSpeakers() as $speaker) {
                    if ($speaker->getStatutSpeaker() === 'Validé') {
                        $sessionSpeakersMap[$sessionId][] = $speaker;
                    }
                }
            }
        }

        $validatedCommentaries = $commentaryRepository->findValidatedCommentariesWithFormations();

        if ($request->isMethod('POST') && $request->request->has('submit-comment-net-pitch')) {
            if (!$this->getUser()) {
                return $this->redirectToRoute('app_register');
                $this->addFlash('comment_login_required', 'Vous devez avoir un compte pour laisser un commentaire.');
            }

            $comment = $request->request->get('comment');
            $formationTitle = $request->request->get('event');

            if (!$comment || !$formationTitle) {
                $this->addFlash('comment_net_pitch_error', 'Tous les champs sont requis pour envoyer votre commentaire.');
                return $this->redirectToRoute('app_net-pitch', [
                    'slug' => $slug,
                    '_fragment' => 'commentaires-formations'
                ]);
            }

            $commentary = new Commentary();
            $commentary->setUser($this->getUser());
            $commentary->setTextCommentary($comment);
            $commentary->setStatutCommentary('En attente');
            $commentary->setCreatedAt(new DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));

            $formationTarget = $formationRepository->findOneBy(['titleNetPitchFormation' => $formationTitle]);
            if ($formationTarget) {
                $commentary->setNetPitchFormation($formationTarget);
            }

            $em->persist($commentary);
            $em->flush();

            $this->addFlash('comment_net_pitch_success', 'Votre commentaire a été envoyé et sera publié après validation.');

            return $this->redirectToRoute('app_net-pitch', [
                'slug' => $slug,
                '_fragment' => 'commentaires-formations'
            ]);
        }

        if ($request->isMethod('POST') && $request->request->has('submit-pre-inscription')) {
            $slug = $request->request->get('slug', $slug);
            $registration = new RegistrationNetPitchFormation();

            $registration->setFirstnameRegistration(trim($request->request->get('first-name-form-formation')));
            $registration->setLastnameRegistration(trim($request->request->get('last-name-form-formation')));
            $registration->setEmailRegistration(trim($request->request->get('email-form-formation')));
            $registration->setTelRegistration(trim($request->request->get('phone-form-formation')));
            $registration->setAfdas($request->request->get('afdas') === '1');
            $registration->setProfessionalProjectRegistration(trim($request->request->get('short-description-form-formation')));
            $registration->setStatutRegistration('En cours');
            $registration->setCreatedAtRegistration(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));

            $cvFile = $request->files->get('cv-upload-form-formation');
            if ($cvFile) {
                $lastName = $registration->getLastnameRegistration();
                $firstName = $registration->getFirstnameRegistration();
                $safeLastName = preg_replace('/[^a-z0-9]/i', '-', strtolower(transliterator_transliterate('Any-Latin; Latin-ASCII', $lastName)));
                $safeFirstName = preg_replace('/[^a-z0-9]/i', '-', strtolower(transliterator_transliterate('Any-Latin; Latin-ASCII', $firstName)));

                $uploadDir = $this->getParameter('cv_directory');

                $extension = $cvFile->guessExtension();
                $baseFilename = 'cv-' . $safeLastName . '-' . $safeFirstName;
                $cvFilename = $baseFilename . '.' . $extension;

                $i = 1;
                while (file_exists($uploadDir . '/' . $cvFilename)) {
                    $cvFilename = $baseFilename . '-' . $i . '.' . $extension;
                    $i++;
                }

                try {
                    $cvFile->move($uploadDir, $cvFilename);
                    $registration->setCvRegistration($cvFilename);
                } catch (FileException $e) {
                    $this->addFlash('preinscription_error', 'Erreur lors du téléchargement du CV.');
                    return $this->redirectToRoute('app_net-pitch', [
                        'slug' => $slug,
                        '_fragment' => 'pre-inscription'
                    ]);
                }
            }

            $formationId = $request->request->get('formation-select');
            $formationSelected = $formationRepository->find($formationId);

            if (!$formationSelected) {
                $this->addFlash('preinscription_error', 'La formation sélectionnée est introuvable.');
                return $this->redirectToRoute('app_net-pitch', [
                    'slug' => $slug,
                    '_fragment' => 'pre-inscription'
                ]);
            }

            $sessionId = $request->request->get('selected-session-form-formation');
            $matchingSession = $sessionRepository->find($sessionId);

            if ($matchingSession) {
                $registration->setSessionNetPitchFormation($matchingSession);
                $registration->setConditionValidated(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
                $em->persist($registration);
                $em->flush();

                $mailerService->sendConfirmationToUser($registration);
                $mailerService->sendNotificationToAdmin($registration);

                $this->addFlash('preinscription_success', 'Votre pré-inscription a bien été enregistrée. Un email de confirmation vous a été envoyé.');
            } else {
                $this->addFlash('preinscription_error', 'La session sélectionnée est introuvable.');
            }

            return $this->redirectToRoute('app_net-pitch', [
                'slug' => $slug,
                '_fragment' => 'pre-inscription'
            ]);
        }

        return $this->render('net-pitch/index.html.twig', [
            'sessions' => $sessions,
            'futureSessions' => $futureSessions,
            'generalCineNetwork' => $generalCineNetwork,
            'commentaries' => $validatedCommentaries,
            'formations' => $formations,
            'formation' => $formation,
            'sessionSpeakersMap' => $sessionSpeakersMap,
            'firstFormation' => $firstFormation,
            'firstFormationSlug' => $firstFormationSlug,
            'previousFormation' => $previousFormation,
            'nextFormation' => $nextFormation,
            'otherHeader' => $otherHeader,
        ]);
    }
}
