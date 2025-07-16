<?php

namespace App\Controller;

use App\Repository\SubCategoryAnnouncementRepository;
use App\Repository\AnnouncementRepository;
use App\Repository\CategoryAnnouncementRepository;
use App\Repository\SessionNetPitchFormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Announcement;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EventRepository;
use App\Repository\ArchivedEventRepository;
use App\Repository\GeneralCineNetworkRepository;
use App\Repository\HeaderRepository;

class AnnouncementController extends AbstractController
{
    #[Route('/annonces', name: 'app_announcement')]
    public function index(
        Request $request,
        SessionNetPitchFormationRepository $sessionRepository,
        CategoryAnnouncementRepository $categoryAnnouncementRepository,
        AnnouncementRepository $announcementRepository,
        SubCategoryAnnouncementRepository $subCategoryAnnouncementRepository,
        EntityManagerInterface $em,
        EventRepository $eventRepository,
        ArchivedEventRepository $archivedEventRepository,
        HeaderRepository $headerRepository,
        GeneralCineNetworkRepository $generalCineNetworkRepository,
    ): Response {
        $generalCineNetwork = $generalCineNetworkRepository->findOneBy([]);

        $otherHeader = $headerRepository->findOneBy([
            'pageTypeHeader' => 'Annonce',
            'draft' => false,
        ]);

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        $sessions = array_filter(
            $sessionRepository->findAll(),
            fn($s) => !$s->isDraft() && !$s->getNetPitchFormation()?->isDraft()
        );

        $futureSessions = array_filter(
            $sessions,
            fn($s) => $s->getStartDateSessionNetPitchFormation() > $now
        );

        $categoriesAnnouncement = $categoryAnnouncementRepository->findAll();
        $announcements = $announcementRepository->findBy([], ['createdAtAnnouncement' => 'DESC']);
        $subCategories = $subCategoryAnnouncementRepository->findAll();

        if ($request->isMethod('POST')) {
            if (!$this->getUser()) {
                $this->addFlash('auth_error_annoncement', 'Vous devez être connecté pour publier une annonce.');
                return $this->redirectToRoute('app_register');
            }

            $availability = new \DateTime($request->request->get('availability'), new \DateTimeZone('Europe/Paris'));
            $availability->setTime(0, 0);
            $expiration = new \DateTime($request->request->get('ad-expiration'), new \DateTimeZone('Europe/Paris'));
            $expiration->setTime(0, 0);
            $today = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $today->setTime(0, 0);

            if ($availability < $today) {
                $this->addFlash('date_error_annoncement', 'La date de disponibilité ne peut pas être antérieure à aujourd’hui.');
                return $this->redirectToRoute('app_announcement', ['_fragment' => 'publier-annonce']);
            }

            if ($expiration <= $availability) {
                $this->addFlash('date_error_annoncement', 'La date de fin doit être postérieure à la date de début, avec au moins un jour d’écart.');
                return $this->redirectToRoute('app_announcement', ['_fragment' => 'publier-annonce']);
            }

            $adType = $request->request->get('ad-type');
            if (!in_array($adType, ['Recrute', 'Disponible'])) {
                $this->addFlash('fomr_error_annoncement', 'Le type d’annonce est invalide.');
                return $this->redirectToRoute('app_announcement', ['_fragment' => 'publier-annonce']);
            }

            $announcement = new Announcement();
            $announcement->setUser($this->getUser());
            $announcement->setTypeAnnouncement($adType);
            $announcement->setDepartmentAnnouncement($request->request->get('department'));
            $announcement->setCityAnnouncement(trim($request->request->get('city')));
            $announcement->setTextAnnouncement(trim($request->request->get('short-description')));
            $announcement->setAvailabilityAnnouncement($availability);
            $announcement->setExpiryAnnouncement($expiration);
            $announcement->setLinkAnnouncement(trim($request->request->get('useful-link')));
            $announcement->setCreatedAtAnnouncement(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $announcement->setRemuneration($request->request->get('is-paid') === 'oui');

            $subCategory = $subCategoryAnnouncementRepository->findOneBy([
                'nameSubCategory' => $request->request->get('job-search')
            ]);
            if ($subCategory) {
                $announcement->setSubCategoryAnnouncement($subCategory);
            }

            $em->persist($announcement);
            $em->flush();

            $this->addFlash('success_annoncement', 'Votre annonce a bien été publiée.');
            return $this->redirectToRoute('app_announcement', ['_fragment' => 'publier-annonce']);
        }

        $events = $eventRepository->findUpcomingEvents();
        $firstEvent = null;

        if (!empty($events)) {
            $firstEvent = $events[0];
        } else {
            $archived = $archivedEventRepository->findLastArchivedEventBeforeNow();
            if ($archived) {
                $firstEvent = $archived->getEvent();
            }
        }

        return $this->render('announcement/index.html.twig', [
            'controller_name' => 'AnnouncementController',
            'sessions' => $sessions,
            'categoriesAnnouncement' => $categoriesAnnouncement,
            'announcements' => $announcements,
            'subCategories' => $subCategories,
            'now' => new \DateTime(),
            'firstEvent' => $firstEvent,
            'otherHeader' => $otherHeader,
            'generalCineNetwork' => $generalCineNetwork,
            'futureSessions' => $futureSessions,
        ]);
    }
}
