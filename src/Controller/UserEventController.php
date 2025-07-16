<?php

namespace App\Controller;

use App\Entity\UserEvent;
use App\Entity\Event;
use App\Repository\UserEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Repository\GeneralCineNetworkRepository;

class UserEventController extends AbstractController
{
    #[Route('/event/{id}/register', name: 'event_register', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function register(
        Event $event,
        EntityManagerInterface $entityManager,
        UserEventRepository $userEventRepository,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse|RedirectResponse {
        $user = $this->getUser();

        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('event_register', $submittedToken))) {
            return $this->json(['success' => false, 'message' => 'Jeton CSRF invalide.'], 400);
        }

        $existing = $userEventRepository->findOneBy(['user' => $user, 'event' => $event]);
        if ($existing) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
            return $request->isXmlHttpRequest()
                ? $this->json(['success' => false, 'message' => 'Déjà inscrit.'])
                : $this->redirectToRoute('app_index');
        }

        $userEvent = new UserEvent();
        $userEvent->setUser($user);
        $userEvent->setEvent($event);
        $userEvent->setRegisteredAt(new \DateTimeImmutable('Europe/Paris'));

        $entityManager->persist($userEvent);
        $entityManager->flush();

        $this->addFlash('success', 'Inscription réussie !');

        return $request->isXmlHttpRequest()
            ? $this->json(['success' => true, 'message' => 'Inscription réussie !', 'eventData' => $this->getEventData($event)])
            : $this->redirectToRoute('app_index');
    }

    #[Route('/event/{id}/unregister', name: 'event_unregister', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function unregister(
        Event $event,
        EntityManagerInterface $entityManager,
        UserEventRepository $userEventRepository,
        MailerInterface $mailer,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        GeneralCineNetworkRepository $cineNetworkRepository,
    ): JsonResponse|RedirectResponse {
        $user = $this->getUser();

        /** @var \App\Entity\User $user */

        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('event_unregister', $submittedToken))) {
            return $this->json(['success' => false, 'message' => 'Jeton CSRF invalide.'], 400);
        }

        $existing = $userEventRepository->findOneBy(['user' => $user, 'event' => $event]);
        if (!$existing) {
            $this->addFlash('warning', 'Vous n\'êtes pas inscrit à cet événement.');
            return $request->isXmlHttpRequest()
                ? $this->json(['success' => false, 'message' => 'Non inscrit.'])
                : $this->redirectToRoute('app_index');
        }

        $entityManager->remove($existing);
        $entityManager->flush();

        $cineNetwork = $cineNetworkRepository->findOneBy([]);
        $fromEmail = $cineNetwork?->getEmailCompany() ?? 'noreply@fallback.com';
        $toEmail = $cineNetwork?->getPersonalEmail() ?? 'admin@exemple.com';


        $email = (new Email())
            ->from($fromEmail)
            ->to($toEmail)
            ->subject('Annulation d\'inscription à un événement')
            ->html(sprintf(
                '<p>%s %s (%s) s\'est désinscrit de l\'événement "%s" prévu le %s.</p>',
                $user->getFirstnameUser(),
                $user->getLastnameUser(),
                $user->getEmail(),
                $event->getTitleEvent(),
                $event->getDateEvent()->format('d/m/Y')
            ));
        $mailer->send($email);

        $this->addFlash('success', 'Votre inscription a été annulée.');

        return $request->isXmlHttpRequest()
            ? $this->json(['success' => true, 'message' => 'Annulation réussie.'])
            : $this->redirectToRoute('app_index');
    }

    private function getEventData(Event $event): array
    {
        $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, null, null, 'd MMMM y');
        $formattedDate = $event->getDateEvent() ? mb_strtoupper($formatter->format($event->getDateEvent()), 'UTF-8') : null;

        $participants = [];
        foreach ($event->getUserEvents() as $userEvent) {
            $user = $userEvent->getUser();
            if ($user) {
                $participants[] = [
                    'id' => $user->getId(),
                    'firstname' => $user->getFirstnameUser(),
                    'lastname' => $user->getLastnameUser(),
                    'picture' => $user->getPictureUser(),
                ];
            }
        }

        $speakers = [];
        foreach ($event->getSpeakers() as $speaker) {
            $speakers[] = [
                'id' => $speaker->getId(),
                'firstname' => $speaker->getFirstNameSpeaker(),
                'lastname' => $speaker->getLastNameSpeaker(),
                'role' => $speaker->getRoleSpeaker(),
                'bio' => $speaker->getBiographySpeaker(),
                'company' => $speaker->getPictureCompanySpeaker(),
                'picture' => $speaker->getPictureSpeaker(),
                'popup' => $speaker->getImgPopUpSpeaker(),
                'type' => $speaker->getTypeSpeaker(),
                'statut' => $speaker->getStatutSpeaker(),
            ];
        }

        $shortFilms = [];
        foreach ($event->getShortFilms() as $film) {
            $speaker = $film->getSpeakers()->first();
            $speakerData = null;

            if ($speaker) {
                $speakerData = [
                    'name' => $speaker->getFirstNameSpeaker() . ' ' . $speaker->getLastNameSpeaker(),
                    'news' => $speaker->getNewsSpeaker(),
                    'bio' => $speaker->getBiographySpeaker(),
                    'search' => $speaker->getSearch(),
                    'role' => $speaker->getRoleSpeaker(),
                    'picture' => $speaker->getPictureSpeaker(),
                    'company' => $speaker->getPictureCompanySpeaker(),
                    'popup' => $speaker->getImgPopUpSpeaker(),
                ];
            }

            $shortFilms[] = [
                'title' => $film->getTitleShortFilm(),
                'genre' => $film->getGenreShortFilm(),
                'duration' => $film->getDurationShortFilm(),
                'production' => $film->getProductionShortFilm(),
                'pitch' => $film->getPitchShortFilm(),
                'poster' => $film->getPosterShortFilm(),
                'popupPoster' => $film->getPosterPopUpShortFilm(),
                'statut' => $film->getStatutShortFilm(),
                'speaker' => $speakerData,
            ];
        }

        return [
            'id' => $event->getId(),
            'title' => $event->getTitleEvent(),
            'text' => $event->getTextEvent(),
            'date' => $formattedDate,
            'img' => $event->getImgEvent(),
            'free' => $event->isFree(),
            'price' => $event->getPriceEvent() ?? 0,
            'type' => $event->getTypeEvent(),
            'proposal' => $event->isShortFilmProposal(),
            'program' => $event->getProgramEvent() ?? '',
            'location' => [
                'street' => $event->getLocation()?->getStreetLocation() ?? '',
                'postalCode' => $event->getLocation()?->getPostalCode() ?? '',
                'city' => $event->getLocation()?->getCityLocation() ?? '',
            ],
            'speakers' => $speakers,
            'shortFilms' => $shortFilms,
            'participants' => $participants,
        ];
    }
}
