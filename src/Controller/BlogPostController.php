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

class BlogPostController extends AbstractController
{
    #[Route('/article/{slug}', name: 'app_blog_post_show')]
    public function show(
        string $slug,
        BlogPostRepository $blogPostRepository,
        BlogCategoryRepository $blogCategoryRepository,
        SessionNetPitchFormationRepository $sessionRepository,
        EventRepository $eventRepository,
        ArchivedEventRepository $archivedEventRepository,
        GeneralCineNetworkRepository $generalCineNetworkRepository,
    ): Response {

        $generalCineNetwork = $generalCineNetworkRepository->findOneBy([]);

        $post = $blogPostRepository->findOneBy(['slugBlogPost' => $slug]);

        if (!$post) {
            throw $this->createNotFoundException('Article non trouvé.');
        }

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

        return $this->render('blog_post/index.html.twig', [
            'controller_name' => 'BlogPostController',
            'post' => $post,
            'sessions' => $sessions,
            'categoriesBlog' => $categoriesBlog,
            'relatedPosts' => $post->getBlogPosts(),
            'metaDescription' => $post->getMetaDescriptionBlogPost(),
            'metaKeywords' => $post->getSeoKeyBlogPost(),
            'firstEvent' => $firstEvent,
            'futureSessions' => $futureSessions,
            'generalCineNetwork' => $generalCineNetwork,
        ]);
    }
}
