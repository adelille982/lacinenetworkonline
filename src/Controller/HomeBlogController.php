<?php

namespace App\Controller;

use App\Repository\BlogCategoryRepository;
use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SessionNetPitchFormationRepository;
use App\Repository\EventRepository;
use App\Repository\ArchivedEventRepository;
use App\Repository\GeneralCineNetworkRepository;
use App\Repository\HeaderRepository;

class HomeBlogController extends AbstractController
{
    #[Route('/blog', name: 'app_home_blog')]
    public function index(SessionNetPitchFormationRepository $sessionRepository, BlogCategoryRepository $blogCategoryRepository, BlogPostRepository $blogPostRepository, EventRepository $eventRepository, ArchivedEventRepository $archivedEventRepository, HeaderRepository $headerRepository, GeneralCineNetworkRepository $generalCineNetworkRepository): Response
    {
        $generalCineNetwork = $generalCineNetworkRepository->findOneBy([]);

        $otherHeader = $headerRepository->findOneBy([
            'pageTypeHeader' => 'Blog',
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

        $postsBlog = $blogPostRepository->findBy([], ['publicationDateBlogPost' => 'DESC']);

        $categoriesBlog = array_filter(
            $blogCategoryRepository->findAll(),
            fn($category) => count($category->getBlogPost()) > 0
        );

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

        return $this->render('home_blog/index.html.twig', [
            'controller_name' => 'HomeBlogController',
            'sessions' => $sessions,
            'categoriesBlog' => $categoriesBlog,
            'postsBlog' => $postsBlog,
            'metaDescriptionCategory' => null,
            'metaKeywordsCategory' => null,
            'firstEvent' => $firstEvent,
            'otherHeader' => $otherHeader,
            'futureSessions' => $futureSessions,
            'generalCineNetwork' => $generalCineNetwork,
        ]);
    }

    #[Route('/blog/categorie/{slug}', name: 'app_blog_category')]
    public function filterByCategory(
        string $slug,
        SessionNetPitchFormationRepository $sessionRepository,
        BlogCategoryRepository $blogCategoryRepository,
        BlogPostRepository $blogPostRepository,
        HeaderRepository $headerRepository,
        EventRepository $eventRepository,
        ArchivedEventRepository $archivedEventRepository,
        GeneralCineNetworkRepository $generalCineNetworkRepository
    ): Response {

        $generalCineNetwork = $generalCineNetworkRepository->findOneBy([]);

        $otherHeader = $headerRepository->findOneBy([
            'pageTypeHeader' => 'Blog',
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
        $categoriesBlog = array_filter(
            $blogCategoryRepository->findAll(),
            fn($category) => count($category->getBlogPost()) > 0
        );

        $activeCategory = $blogCategoryRepository->findOneBy(['slugBlogCategory' => $slug]);

        if (!$activeCategory) {
            return $this->redirectToRoute('app_home_blog');
        }

        $postsBlog = $blogPostRepository->findAll();

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

        return $this->render('home_blog/index.html.twig', [
            'controller_name' => 'HomeBlogController',
            'sessions' => $sessions,
            'categoriesBlog' => $categoriesBlog,
            'postsBlog' => $postsBlog,
            'activeCategorySlug' => $slug,
            'metaDescriptionCategory' => $activeCategory->getMetaDescriptionBlogCategory(),
            'metaKeywordsCategory' => $activeCategory->getSeoKeyBlogCategory(),
            'otherHeader' => $otherHeader,
            'firstEvent' => $firstEvent,
            'futureSessions' => $futureSessions,
            'generalCineNetwork' => $generalCineNetwork,

        ]);
    }
}
