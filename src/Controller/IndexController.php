<?php

namespace App\Controller;

use IntlDateFormatter;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\BlogPost;
use App\Entity\Commentary;
use App\Repository\ArchivedEventRepository;
use App\Repository\BlogPostRepository;
use App\Repository\CommentaryRepository;
use App\Repository\EventRepository;
use App\Repository\GeneralCineNetworkRepository;
use App\Repository\HeaderHomeRepository;
use App\Repository\PartnerRepository;
use App\Repository\SessionNetPitchFormationRepository;
use App\Repository\ShortFilmRepository;
use App\Repository\SpeakerRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(
        Request $request,
        HeaderHomeRepository $headerHomeRepository,
        GeneralCineNetworkRepository $generalCineNetworkRepository,
        EventRepository $eventRepository,
        ShortFilmRepository $shortFilmRepository,
        SpeakerRepository $speakerRepository,
        ArchivedEventRepository $archivedEventRepository,
        PartnerRepository $partnerRepository,
        MailerInterface $mailer,
        SessionNetPitchFormationRepository $sessionRepository,
        CommentaryRepository $commentaryRepository,
        EntityManagerInterface $em,
        BlogPostRepository $blogPostRepository,
        GeneralCineNetworkRepository $cineNetworkRepository,
    ): Response {

        $headerHome = $headerHomeRepository->findOneBy(['draft' => false], ['id' => 'DESC']);

        $generalCineNetwork = $generalCineNetworkRepository->findOneBy([]);

        $shortFilms = $shortFilmRepository->findAllOrderedByNewest();

        $speakers = $speakerRepository->findAll();

        $blogPosts = $blogPostRepository->findBy(['draft' => false], ['publicationDateBlogPost' => 'DESC'], 3);
        $blogPosts = array_filter($blogPosts, function (BlogPost $post) {
            return $post->getPublicationDateBlogPost() <= new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        });

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        $sessions = array_filter(
            $sessionRepository->findAll(),
            fn($s) => !$s->isDraft() && !$s->getNetPitchFormation()?->isDraft()
        );

        $futureSessions = array_filter(
            $sessions,
            fn($s) => $s->getStartDateSessionNetPitchFormation() > $now
        );

        $partners = $partnerRepository->findAll();

        $archivedEvents = $archivedEventRepository->findArchivedEventsWithValidBackToImage();
        $hasArchivedEventsForCommentary = !empty($archivedEvents);

        $events = $eventRepository->findUpcomingEvents();
        $firstEvent = null;
        $otherEvents = [];
        $eventIsArchived = false;

        if (!empty($events)) {
            $firstEvent = array_shift($events);
            $otherEvents = array_values($events);
        } else {
            $lastArchived = $archivedEventRepository->findLastArchivedEventBeforeNow(); // ← pour le bloc principal
            if ($lastArchived) {
                $firstEvent = $lastArchived->getEvent();
                $eventIsArchived = true;
            }
        }

        $allEventIds = $firstEvent
            ? array_merge([$firstEvent->getId()], array_map(fn($e) => $e->getId(), $otherEvents))
            : [];

        $validatedEventComments = $commentaryRepository->findValidatedCommentariesWithEvents();

        if ($request->isMethod('POST') && $request->request->has('submit-comment-network')) {
            if (!$this->getUser()) {
                return $this->redirectToRoute('app_register');
                $this->addFlash('comment_login_required', 'Vous devez avoir un compte pour laisser un commentaire.');
            }

            $comment = $request->request->get('comment');
            $eventId = $request->request->get('event');

            $archivedEvent = $archivedEventRepository->find($eventId);

            if (!$comment || !$archivedEvent) {
                $this->addFlash('comment_network_error', 'Tous les champs sont requis pour envoyer votre commentaire.');
                return $this->redirectToRoute('app_index', ['_fragment' => 'commentaires-network']);
            }

            $commentary = new Commentary();
            $commentary->setUser($this->getUser());
            $commentary->setTextCommentary(trim($comment));
            $commentary->setStatutCommentary('En attente');
            $commentary->setCreatedAt(new DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $commentary->setArchivedEvent($archivedEvent);

            $em->persist($commentary);
            $em->flush();

            $this->addFlash('comment_network_success', 'Votre commentaire a bien été soumis et sera publié après validation.');
            return $this->redirectToRoute('app_index', ['_fragment' => 'commentaires-network']);
        }

        if ($request->isMethod('POST') && $request->request->has('submit-contact-home')) {
            $firstname = $request->request->get('firstname-form-contact');
            $lastname = $request->request->get('lastname-form-contact');
            $email = $request->request->get('email-form-contact-form-contact');
            $event = $request->request->get('event-form-contact');
            $message = $request->request->get('comment-form-contact');

            $cineNetwork = $cineNetworkRepository->findOneBy([]);
            $emailFrom = $cineNetwork ? $cineNetwork->getEmailCompany() : 'contact@les-petits-createurs.fr';
            $emailTo = $cineNetwork ? $cineNetwork->getPersonalEmail() : 'a.delille982@gmail.com';

            $emailMessage = (new Email())
                ->from($emailFrom)
                ->to($emailTo)
                ->subject('Nouveau message de contact')
                ->html($this->renderView('index/contact_message.html.twig', [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email,
                    'event' => $event,
                    'message' => $message,
                ]));

            $mailer->send($emailMessage);

            $this->addFlash('success_contact_home', 'Votre message a été envoyé avec succès!');

            return $this->redirectToRoute('app_index', ['_fragment' => 'nous-contacter']);
        }

        if ($request->isMethod('POST') && $request->request->has('submit-shortfilm-proposal')) {
            try {
                $firstname = strtolower(trim($request->request->get('speakerFirstname')));
                $lastname = strtolower(trim($request->request->get('speakerLastname')));
                $slugBase = preg_replace('/[^a-z0-9\-]/', '', $lastname . '-' . $firstname);

                $speaker = new \App\Entity\Speaker();
                $speaker
                    ->setStatutSpeaker('Proposé')
                    ->setTypeSpeaker('Externe')
                    ->setRoleSpeaker('A définir')
                    ->setEmailSpeaker(trim($request->request->get('speakerEmail')))
                    ->setTelSpeaker(trim($request->request->get('speakertelephone')))
                    ->setFirstNameSpeaker($firstname)
                    ->setLastNameSpeaker($lastname)
                    ->setBiographySpeaker(trim($request->request->get('speakerBiography')))
                    ->setInstagramSpeaker(trim($request->request->get('speakerInstagram')))
                    ->setFacebookSpeaker(trim($request->request->get('speakerFacebook')))
                    ->setSearch(trim($request->request->get('speakerSearch')))
                    ->setNewsSpeaker(trim($request->request->get('speakerNews')))
                    ->setDraft(true);

                $profileDir = $this->getParameter('kernel.project_dir') . '/public/images/intervenants/realisateur-et-stagiaire/realisateur-et-stagiaire-profil/';
                $popupDir = $this->getParameter('kernel.project_dir') . '/public/images/intervenants/realisateur-et-stagiaire/realisateur-et-stagiaire-pop-up/';
                $companyDir = $this->getParameter('kernel.project_dir') . '/public/images/intervenants/logos-entreprises-intervenants';

                $imageMappings = [
                    'speakerProfilePicture' => ['method' => 'setPictureSpeaker', 'dir' => $profileDir, 'suffix' => 'photo-profil'],
                    'speakerPopupPicture' => ['method' => 'setImgPopUpSpeaker', 'dir' => $popupDir, 'suffix' => 'photo-pop-up'],
                    'speakerCompanyLogo' => ['method' => 'setPictureCompanySpeaker', 'dir' => $companyDir, 'suffix' => 'logo-societe'],
                ];

                foreach ($imageMappings as $input => $conf) {
                    $file = $request->files->get($input);
                    if ($file) {
                        $ext = $file->guessExtension() ?? 'bin';
                        $filename = $slugBase . '-' . $conf['suffix'] . '.' . $ext;
                        $file->move($conf['dir'], $filename);
                        $speaker->{$conf['method']}($filename);
                    }
                }

                for ($i = 1; $i <= 3; $i++) {
                    $file = $request->files->get("speakerImg$i");
                    if ($file) {
                        $ext = $file->guessExtension() ?? 'bin';
                        $filename = $slugBase . '-photo-complementaire' . $i . '.' . $ext;
                        $file->move($profileDir, $filename);
                        $setter = "setImgSpeakerProposal$i";
                        $speaker->$setter($filename);
                    }
                }

                $title = strtolower(trim($request->request->get('titleShortFilmProposal')));
                $titleSlug = preg_replace('/[^a-z0-9]+/', '-', $title);
                $realisedBy = '-realise-par-' . $slugBase;

                $shortFilm = new \App\Entity\ShortFilm();
                $shortFilm
                    ->setStatutShortFilm('Proposé')
                    ->setDraft(true)
                    ->setTitleShortFilm($title)
                    ->setDurationShortFilm(trim($request->request->get('durationShortFilmProposal')))
                    ->setGenreShortFilm(trim($request->request->get('genreShortFilmProposal')))
                    ->setProductionShortFilm(trim($request->request->get('productionShortFilmProposal')))
                    ->setPitchShortFilm(trim($request->request->get('pitchShortFilmProposal')))
                    ->setLinkShortFilmProposal(trim($request->request->get('linkShortFilm')))
                    ->setLinkTrailerShortFilmProposal(trim($request->request->get('trailerShortFilm')))
                    ->setFormatDcp($request->request->get('formatDCPProposal') === '1');

                $filmDir = $this->getParameter('kernel.project_dir') . '/public/images/courts-metrages';
                $popupDir = $filmDir . '/courts-metrages-pop-up';

                $poster = $request->files->get('posterShortFilmProposal');
                if ($poster) {
                    $ext = $poster->guessExtension() ?? 'bin';
                    $filename = $titleSlug . '-affiche-film' . $realisedBy . '.' . $ext;
                    $poster->move($filmDir, $filename);
                    $shortFilm->setPosterShortFilm($filename);
                }

                $popupPoster = $request->files->get('posterPopUpShortFilmProposal');
                if ($popupPoster) {
                    $ext = $popupPoster->guessExtension() ?? 'bin';
                    $filename = $titleSlug . '-img-pop-up' . $realisedBy . '.' . $ext;
                    $popupPoster->move($popupDir, $filename);
                    $shortFilm->setPosterPopUpShortFilm($filename);
                }

                for ($i = 1; $i <= 5; $i++) {
                    $file = $request->files->get("imgShortFilmProposal$i");
                    if ($file) {
                        $ext = $file->guessExtension() ?? 'bin';
                        $filename = $titleSlug . '-img-film-complementaire' . $i . $realisedBy . '.' . $ext;
                        $file->move($filmDir, $filename);
                        $setter = "setImgProposal$i";
                        $shortFilm->$setter($filename);
                    }
                }

                $shortFilm->addSpeaker($speaker);
                $em->persist($speaker);
                $em->persist($shortFilm);
                $em->flush();

                $this->addFlash('success_proposal_short_film', 'Votre proposition de court-métrage a bien été enregistrée !');
            } catch (\Throwable $e) {
                $this->addFlash('error_proposal_short_film', 'Une erreur est survenue pendant l\'envoi. Veuillez réessayer.');
            }

            return $this->redirect('/#open-shortfilm-popup');
        }

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'headerHome' => $headerHome,
            'generalCineNetwork' => $generalCineNetwork,
            'sessions' => $sessions,
            'shortFilms' => $shortFilms,
            'speakers' => $speakers,
            'archivedEvents' => $archivedEvents,
            'partners' => $partners,
            'firstEvent' => $firstEvent,
            'otherEvents' => $otherEvents,
            'validatedEventComments' => $validatedEventComments,
            'blogPosts' => $blogPosts,
            'allEventIds' => $allEventIds,
            'eventIsArchived' => $eventIsArchived,
            'futureSessions' => $futureSessions,
            'hasArchivedEventsForCommentary' => $hasArchivedEventsForCommentary,

        ]);
    }

    #[Route('/api/event/{id}', name: 'api_event_data', methods: ['GET'])]
    public function apiEventData(
        EventRepository $eventRepository,
        ArchivedEventRepository $archivedEventRepository,
        int $id
    ): JsonResponse {
        $event = $eventRepository->find($id);
        $eventIsArchived = false;

        if (!$event) {
            $archivedEvent = $archivedEventRepository->find($id);
            if (!$archivedEvent || $archivedEvent->isDraft()) {
                return $this->json(['error' => 'Événement introuvable'], 404);
            }
            $event = $archivedEvent->getEvent();
            $eventIsArchived = true;
        } else {
            $archivedEvent = $archivedEventRepository->findOneBy([
                'event' => $event,
                'draft' => false,
            ]);
            $eventIsArchived = $archivedEvent !== null;
        }

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
                'bio' => $this->sanitize($speaker->getBiographySpeaker()),
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
                    'news' => $this->sanitize($speaker->getNewsSpeaker()),
                    'bio' => $this->sanitize($speaker->getBiographySpeaker()),
                    'search' => $this->sanitize($speaker->getSearch()),
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
                'pitch' => $this->sanitize($film->getPitchShortFilm()),
                'poster' => $film->getPosterShortFilm(),
                'popupPoster' => $film->getPosterPopUpShortFilm(),
                'statut' => $film->getStatutShortFilm(),
                'speaker' => $speakerData,
            ];
        }

        return $this->json([
            'archived' => $eventIsArchived,
            'id' => $event->getId(),
            'title' => $event->getTitleEvent(),
            'text' => $event->getTextEvent(),
            'date' => $formattedDate,
            'rawDate' => $event->getDateEvent()?->format('Y-m-d'),
            'img' => $event->getImgEvent(),
            'free' => $event->isFree(),
            'price' => $event->getPriceEvent() ?? 0,
            'type' => $event->getTypeEvent(),
            'proposal' => $eventIsArchived ? false : $event->isShortFilmProposal(),
            'program' => $event->getProgramEvent() ?? '',
            'location' => [
                'street' => $event->getLocation()?->getStreetLocation() ?? '',
                'postalCode' => $event->getLocation()?->getPostalCode() ?? '',
                'city' => $event->getLocation()?->getCityLocation() ?? '',
            ],
            'speakers' => $speakers,
            'shortFilms' => $shortFilms,
            'participants' => $participants,
        ]);
    }

    #[Route('/api/archived-events', name: 'api_archived_events', methods: ['GET'])]
    public function apiArchivedEvents(ArchivedEventRepository $archivedEventRepository): JsonResponse
    {
        $archivedEvents = $archivedEventRepository->findArchivedEventsWithValidBackToImage();

        $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, null, null, 'd MMMM y');

        $data = array_map(function ($archived) use ($formatter) {
            $event = $archived->getEvent();
            $backToImage = $archived->getBackToImage();
            $images = $backToImage ? $backToImage->getImageBackToImages() : [];

            return [
                'id' => $archived->getId(),
                'eventId' => $event->getId(),
                'title' => $event->getTitleEvent(),
                'date' => $event->getDateEvent() ? mb_strtoupper($formatter->format($event->getDateEvent()), 'UTF-8') : null,
                'img' => $event->getImgEvent(),
                'backToImageText' => $backToImage?->getTextBackToImage(),
                'images' => array_map(fn($img) => $img->getImgBackToImage(), $images->toArray()),
            ];
        }, $archivedEvents);

        return $this->json($data);
    }

    private function sanitize(?string $text): string
    {
        return strip_tags($text ?? '');
    }
}
