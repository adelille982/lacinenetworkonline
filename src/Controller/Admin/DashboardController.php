<?php

namespace App\Controller\Admin;

use App\Entity\ArchivedEvent;
use App\Entity\Announcement;
use App\Entity\ArchivedSessionNetPitchFormation;
use App\Entity\BackToImage;
use App\Entity\BlogCategory;
use App\Entity\BlogPost;
use App\Entity\CategoryAnnouncement;
use App\Entity\Commentary;
use App\Entity\Event;
use App\Entity\Gain;
use App\Entity\GeneralCineNetwork;
use App\Entity\Header;
use App\Entity\HeaderHome;
use App\Entity\HeaderHomeImg;
use App\Entity\ImageBackToImage;
use App\Entity\Location;
use App\Entity\NetPitchFormation;
use App\Entity\Partner;
use App\Entity\Postulate;
use App\Entity\RegistrationNetPitchFormation;
use App\Entity\SessionNetPitchFormation;
use App\Entity\ShortFilm;
use App\Entity\Speaker;
use App\Entity\SubCategoryAnnouncement;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\UserEvent;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(AdminCrudController::class)->generateUrl());
    }


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('La Ciné Network');
    }

    public function configureMenuItems(): iterable
    {

        $isGranted = fn(string $role) => $this->isGranted($role);

        yield MenuItem::linkToUrl('🏠 Retour au site', 'fas fa-arrow-left', $this->generateUrl('app_index'));

        // Section Configuration
        if ($isGranted('ROLE_ADMIN') || $isGranted('ROLE_SUB_ADMIN')) {
            yield MenuItem::section('⚙️ Configuration Générale');
            yield MenuItem::linkToUrl('Gestionnaire de fichiers', 'fas fa-folder-open', '/filemanager?conf=default')
                ->setLinkTarget('_blank');
            yield MenuItem::subMenu('🔧 Réglages généraux', 'fas fa-sliders-h')->setSubItems([
                MenuItem::linkToCrud('Informations', 'fas fa-info-circle', GeneralCineNetwork::class)
                    ->setController(GeneralCineNetworkCrudController::class),
                MenuItem::linkToCrud('RGPD', 'fas fa-user-shield', GeneralCineNetwork::class)
                    ->setController(RgpdCrudController::class),
                MenuItem::linkToCrud('Images globales', 'fas fa-images', GeneralCineNetwork::class)
                    ->setController(ImgGeneralCineNetworkCrudController::class),
                MenuItem::linkToCrud('Partenaires', 'fas fa-handshake', Partner::class)
                    ->setController(PartnerCrudController::class),
            ]);

            // Headers
            yield MenuItem::section('🧭 Contenu des en-têtes');
            yield MenuItem::subMenu('En-têtes des pages', 'fas fa-layer-group')->setSubItems([
                MenuItem::linkToCrud('Accueil (texte)', 'fas fa-font', HeaderHome::class)
                    ->setController(HeaderHomeCrudController::class),
                MenuItem::linkToCrud('Accueil (images)', 'fas fa-image', HeaderHomeImg::class)
                    ->setController(HeaderHomeImgCrudController::class),
                MenuItem::linkToCrud('Autres pages', 'fas fa-file-alt', Header::class)
                    ->setController(HeaderCrudController::class),
            ]);
        }

        // Utilisateurs
        if ($isGranted('ROLE_ADMIN') || $isGranted('ROLE_SUB_ADMIN') || $isGranted('ROLE_SUB_ADMIN_GESTION')) {
            yield MenuItem::section('👤 Utilisateurs');
            yield MenuItem::linkToCrud('Administrateurs', 'fas fa-user-shield', User::class)
                ->setController(AdminCrudController::class);
            yield MenuItem::linkToCrud('Tous les utilisateurs', 'fas fa-users', User::class);
            yield MenuItem::linkToCrud('Comptes non vérifiés', 'fas fa-user-clock', User::class)
                ->setController(IncompleteUserCrudController::class);
        }

        // Section lieux
        if ($isGranted('ROLE_ADMIN') || $isGranted('ROLE_SUB_ADMIN') || $isGranted('ROLE_SUB_ADMIN_GESTION')) {
            yield MenuItem::section('📍 Lieux');

            yield MenuItem::subMenu('Entreprise', 'fas fa-university')->setSubItems([
                MenuItem::linkToCrud('Locaux de l\'entreprise', 'fas fa-building', Location::class)
                    ->setController(LocationEntrepriseCrudController::class),
            ]);

            yield MenuItem::subMenu('Événements', 'fas fa-map-marker-alt')->setSubItems([
                MenuItem::linkToCrud('Lieux d\'événements', 'fas fa-map-marked-alt', Location::class)
                    ->setController(LocationEventCrudController::class),
            ]);
            yield MenuItem::subMenu('Formations', 'fas fa-university')->setSubItems([
                MenuItem::linkToCrud('Centres de formation', 'fas fa-school', Location::class)
                    ->setController(LocationFormationCrudController::class),
            ]);

            // Intervenants
            yield MenuItem::section('🎤 Intervenants');
            yield MenuItem::subMenu('Tous les intervenants', 'fas fa-users')->setSubItems([
                MenuItem::linkToCrud('Entreprise', 'fas fa-building', Speaker::class)
                    ->setController(CompanySpeakerCrudController::class),
                MenuItem::linkToCrud('Formateurs', 'fas fa-chalkboard-teacher', Speaker::class)
                    ->setController(TrainerCrudController::class),
                MenuItem::linkToCrud('Jury', 'fas fa-balance-scale', Speaker::class)
                    ->setController(JuryCrudController::class),
                MenuItem::linkToCrud('Réalisateurs', 'fas fa-video', Speaker::class)
                    ->setController(ProducerCrudController::class),
                MenuItem::linkToCrud('Stagiaires', 'fas fa-user-graduate', Speaker::class)
                    ->setController(InternCrudController::class),
                MenuItem::linkToCrud('Externe', 'fas fa-user-plus', Speaker::class)
                    ->setController(ProposalCrudController::class),
            ]);

            // Contenu
            yield MenuItem::section('🎞️ Courts-métrages');
            yield MenuItem::linkToCrud('Tous les courts métrages', 'fas fa-film', ShortFilm::class)
                ->setController(TotalShortFilmCrudController::class);
            yield MenuItem::linkToCrud('Courts métrages produits', 'fas fa-clapperboard', ShortFilm::class)
                ->setController(ShortFilmProductCrudController::class);
            yield MenuItem::linkToCrud('Courts métrages à financer', 'fas fa-piggy-bank', ShortFilm::class)
                ->setController(ToFinanceShortFilmCrudController::class);
            yield MenuItem::linkToCrud('Courts métrages proposés', 'fas fa-lightbulb', ShortFilm::class)
                ->setController(ProposalShortFilmCrudController::class);
        }

        // Événements
        if ($isGranted('ROLE_ADMIN') || $isGranted('ROLE_SUB_ADMIN') || $isGranted('ROLE_SUB_ADMIN_EVENT')) {
            yield MenuItem::section('📅 Événements');
            yield MenuItem::subMenu('Gestion des événements', 'fas fa-calendar-alt')->setSubItems([
                MenuItem::linkToCrud('Inscriptions', 'fas fa-list', UserEvent::class)
                    ->setController(UserEventCrudController::class),
                MenuItem::linkToCrud('À venir', 'fas fa-calendar-check', Event::class)
                    ->setController(CurrentEventCrudController::class),
                MenuItem::linkToCrud('Archivés', 'fas fa-box-archive', ArchivedEvent::class)
                    ->setController(ArchivedEventCrudController::class),
            ]);
            yield MenuItem::subMenu('Retour sur images', 'fas fa-photo-film')->setSubItems([
                MenuItem::linkToCrud('Retour sur images', 'fas fa-photo-video', BackToImage::class)
                    ->setController(BackToImageCrudController::class),
                MenuItem::linkToCrud('Images retour sur images', 'fas fa-photo-video', ImageBackToImage::class)
                    ->setController(ImageBackToImageCrudController::class),
            ]);
        }

        // Formations
        if ($isGranted('ROLE_ADMIN') || $isGranted('ROLE_SUB_ADMIN') || $isGranted('ROLE_SUB_ADMIN_FORMATION')) {
            yield MenuItem::section('🎓 Formations');
            yield MenuItem::subMenu('Gestion des formations', 'fas fa-chalkboard-teacher')->setSubItems([
                MenuItem::linkToCrud('Lots à gagner', 'fas fa-gift', Gain::class)
                    ->setController(GainCrudController::class),

                MenuItem::linkToCrud('Formations', 'fas fa-book-open', NetPitchFormation::class)
                    ->setController(NetPitchFormationCrudController::class),

                MenuItem::linkToCrud('Sessions à venir', 'fas fa-calendar-alt', SessionNetPitchFormation::class)
                    ->setController(SessionNetPitchFormationCrudController::class),

                MenuItem::linkToCrud('Sessions en cours', 'fas fa-hourglass-start', RegistrationNetPitchFormation::class)
                    ->setController(CurrentSessionNetPitchFormationCrudController::class),

                MenuItem::linkToCrud('Sessions archivées', 'fas fa-archive', ArchivedSessionNetPitchFormation::class)
                    ->setController(ArchivedSessionNetPitchFormationCrudController::class),

                MenuItem::linkToCrud('Inscriptions en cours', 'fas fa-user-clock', RegistrationNetPitchFormation::class)
                    ->setController(UnverifiedRegistrationNetPitchFormationCrudController::class),

                MenuItem::linkToCrud('Inscriptions validées', 'fas fa-user-check', RegistrationNetPitchFormation::class)
                    ->setController(RegistrationValidatedNetPitchFormationCrudController::class),

                MenuItem::linkToCrud('Inscriptions obsolètes', 'fas fa-user-times', RegistrationNetPitchFormation::class)
                    ->setController(RegistrationOutdatedNetPitchFormationCrudController::class),
            ]);
        }

        // Blog
        if ($isGranted('ROLE_ADMIN') || $isGranted('ROLE_SUB_ADMIN') || $isGranted('ROLE_SUB_ADMIN_BLOG')) {
            yield MenuItem::section('📰 Blog');
            yield MenuItem::subMenu('Articles et catégories', 'fas fa-blog')->setSubItems([
                MenuItem::linkToCrud('Catégories', 'fas fa-folder', BlogCategory::class)
                    ->setController(BlogCategoryCrudController::class),
                MenuItem::linkToCrud('Articles', 'fas fa-pen', BlogPost::class)
                    ->setController(BlogPostCrudController::class),
            ]);
        }

        // Commentaires
        if ($isGranted('ROLE_ADMIN') || $isGranted('ROLE_SUB_ADMIN') || $isGranted('ROLE_SUB_ADMIN_COMMENTARY')) {
            yield MenuItem::section('💬 Gestion des commentaires');
            yield MenuItem::subMenu('Commentaires', 'fas fa-comments')->setSubItems([
                MenuItem::linkToCrud('En ligne', 'fas fa-check-circle', Commentary::class)
                    ->setController(OnlineCommentaryCrudController::class),
                MenuItem::linkToCrud('À vérifier', 'fas fa-exclamation-triangle', Commentary::class)
                    ->setController(UnverifiedCommentaryCrudController::class),
                MenuItem::linkToCrud('Sur les événements', 'fas fa-calendar-day', Commentary::class)
                    ->setController(CommentaryNetworkCrudController::class),
                MenuItem::linkToCrud('Sur les formations', 'fas fa-user-graduate', Commentary::class)
                    ->setController(CommentaryNetPitchCrudController::class),
            ]);
        }

        // Annonces
        if (
            $isGranted('ROLE_ADMIN') ||
            $isGranted('ROLE_SUB_ADMIN') ||
            $isGranted('ROLE_SUB_ADMIN_ANNOUNCEMENT')
        ) {
            yield MenuItem::section('📢 Annonces');

            yield MenuItem::subMenu('Annonces', 'fas fa-bullhorn')->setSubItems([
                MenuItem::linkToCrud('Toutes les annonces', 'fas fa-folder-open', Announcement::class)
                    ->setController(TotalAnnouncementCrudController::class),
                MenuItem::linkToCrud('Annonces en cours', 'fas fa-clock', Announcement::class)
                    ->setController(OngoingAnnouncementCrudController::class),
                MenuItem::linkToCrud('Annonces expirées', 'fas fa-hourglass-end', Announcement::class)
                    ->setController(ExpiredAnnouncementCrudController::class),
                MenuItem::linkToCrud('Catégories', 'fas fa-tags', CategoryAnnouncement::class)
                    ->setController(CategoryAnnouncementCrudController::class),
                MenuItem::linkToCrud('Sous-catégories', 'fas fa-tag', SubCategoryAnnouncement::class)
                    ->setController(SubCategoryAnnouncementCrudController::class),
            ]);
        }

        // Candidatures internes / CV
        if ($isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('📄 Candidatures');
            yield MenuItem::linkToCrud('CV - La Ciné Network', 'fas fa-file-signature', Postulate::class);
        }
    }
}
