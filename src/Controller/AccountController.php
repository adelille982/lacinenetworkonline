<?php

namespace App\Controller;

use App\Repository\CommentaryRepository;
use App\Entity\Event;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserEventRepository;
use App\Entity\Announcement;
use App\Repository\AnnouncementRepository;
use App\Repository\SessionNetPitchFormationRepository;
use App\Repository\SubCategoryAnnouncementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Repository\GeneralCineNetworkRepository;

class AccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_account')]
    public function index(
        Request $request,
        Security $security,
        SessionNetPitchFormationRepository $sessionRepository,
        AnnouncementRepository $announcementRepository,
        SubCategoryAnnouncementRepository $subCategoryAnnouncementRepository,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserEventRepository $userEventRepository,
        CommentaryRepository $commentaryRepository,
        GeneralCineNetworkRepository $generalCineNetworkRepository,
    ): Response {

        $generalCineNetwork = $generalCineNetworkRepository->findOneBy([]);

        $user = $security->getUser();

        if (!$user || !$this->isGrantedAny([
            'ROLE_USER_NETWORK',
            'ROLE_ADMIN',
            'ROLE_SUB_ADMIN',
            'ROLE_SUB_ADMIN_FORMATION',
            'ROLE_SUB_ADMIN_ANNOUNCEMENT',
            'ROLE_SUB_ADMIN_EVENT',
            'ROLE_SUB_ADMIN_BLOG',
            'ROLE_SUB_ADMIN_COMMENTARY',
            'ROLE_SUB_ADMIN_GESTION',
        ])) {
            throw $this->createAccessDeniedException('Vous n’avez pas les autorisations nécessaires.');
        }

        if ($request->isMethod('POST') && $request->request->has('submit-announcement-account')) {
            if (!$user) {
                $this->addFlash('auth_error_annoncement_account', 'Vous devez être connecté pour publier une annonce.');
                return $this->redirectToRoute('app_register');
            }

            $submittedToken = $request->request->get('_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('new_announcement', $submittedToken))) {
                $this->addFlash('date_error_annoncement_account', 'Jeton CSRF invalide.');
                return $this->redirectToRoute('app_account', ['_fragment' => 'publier-annonce-mon-compte']);
            }

            $availability = new \DateTime($request->request->get('availability-account'), new \DateTimeZone('Europe/Paris'));
            $availability->setTime(0, 0);
            $expiration = new \DateTime($request->request->get('ad-expiration-account'), new \DateTimeZone('Europe/Paris'));
            $expiration->setTime(0, 0);
            $today = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $today->setTime(0, 0);

            if ($availability < $today) {
                $this->addFlash('date_error_annoncement_account', 'La date de disponibilité ne peut pas être antérieure à aujourd’hui.');
                return $this->redirectToRoute('app_account', ['_fragment' => 'publier-annonce-mon-compte']);
            }

            if ($expiration <= $availability) {
                $this->addFlash('date_error_annoncement_account', 'La date de fin doit être postérieure à la date de début, avec au moins un jour d’écart.');
                return $this->redirectToRoute('app_account', ['_fragment' => 'publier-annonce-mon-compte']);
            }

            $adType = $request->request->get('ad-type-account');
            if (!in_array($adType, ['Recrute', 'Disponible'])) {
                $this->addFlash('form_error_annoncement_account', 'Le type d’annonce est invalide.');
                return $this->redirectToRoute('app_account', ['_fragment' => 'publier-annonce-mon-compte']);
            }

            $announcement = new Announcement();
            $announcement->setUser($user);
            $announcement->setTypeAnnouncement($adType);
            $announcement->setDepartmentAnnouncement($request->request->get('department-account'));
            $announcement->setCityAnnouncement(trim($request->request->get('city-account')));
            $announcement->setTextAnnouncement(trim($request->request->get('short-description-account')));
            $announcement->setAvailabilityAnnouncement($availability);
            $announcement->setExpiryAnnouncement($expiration);
            $announcement->setLinkAnnouncement(trim($request->request->get('useful-link-account')));
            $announcement->setCreatedAtAnnouncement(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $announcement->setRemuneration($request->request->get('is-paid-account') === 'oui');

            $subCategory = $subCategoryAnnouncementRepository->findOneBy([
                'nameSubCategory' => $request->request->get('job-search-account')
            ]);
            if ($subCategory) {
                $announcement->setSubCategoryAnnouncement($subCategory);
            }

            $em->persist($announcement);
            $em->flush();

            $this->addFlash('success_annoncement_account', 'Votre annonce a bien été publiée.');
            return $this->redirectToRoute('app_account', ['_fragment' => 'publier-annonce-mon-compte']);
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
        $userAnnouncements = $announcementRepository->findBy(['user' => $user], ['createdAtAnnouncement' => 'DESC']);
        $subCategories = $subCategoryAnnouncementRepository->findAll();
        $userEvents = $userEventRepository->findBy(['user' => $user]);
        $userCommentariesFormations = $commentaryRepository->findValidatedFormationCommentariesByUser($user);
        $userCommentariesEvents = $commentaryRepository->findValidatedEventCommentariesByUser($user);


        return $this->render('account/index.html.twig', [
            'sessions' => $sessions,
            'userAnnouncements' => $userAnnouncements,
            'subCategories' => $subCategories,
            'now' => new \DateTime(),
            'user' => $user,
            'csrfTokenNewAnnouncement' => $csrfTokenManager->getToken('new_announcement')->getValue(),
            'userEvents' => $userEvents,
            'userCommentariesFormations' => $userCommentariesFormations,
            'userCommentariesEvents' => $userCommentariesEvents,
            'futureSessions' => $futureSessions,
            'generalCineNetwork' => $generalCineNetwork,
        ]);
    }

    private function isGrantedAny(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                return true;
            }
        }
        return false;
    }

    #[Route('/mon-compte/update-picture', name: 'app_account_update_picture', methods: ['POST'])]
    public function updatePicture(
        Request $request,
        Security $security,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        CsrfTokenManagerInterface $csrfTokenManager
    ): RedirectResponse {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('pictureUser');
        $user = $security->getUser();

        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('update_picture', $submittedToken))) {
            $this->addFlash('danger_picture_account', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_account');
        }

        /** @var \App\Entity\User $user */
        if ($file && $user) {
            if ($file->getSize() > 2 * 1024 * 1024) {
                $this->addFlash('danger_picture_account', 'L’image ne doit pas dépasser 2 Mo.');
                return $this->redirectToRoute('app_account');
            }

            $imageInfo = getimagesize($file->getPathname());
            if (!$imageInfo) {
                $this->addFlash('danger_picture_account', 'Le fichier n’est pas une image valide.');
                return $this->redirectToRoute('app_account');
            }

            [$width, $height] = $imageInfo;
            if ($width > 1920 || $height > 1080) {
                $this->addFlash('danger_picture_account', 'L’image ne doit pas dépasser 1920 x 1080 pixels.');
                return $this->redirectToRoute('app_account');
            }

            $src = match ($imageInfo['mime']) {
                'image/jpeg' => imagecreatefromjpeg($file->getPathname()),
                'image/png'  => imagecreatefrompng($file->getPathname()),
                'image/webp' => imagecreatefromwebp($file->getPathname()),
                default => null
            };

            if (!$src) {
                $this->addFlash('danger_picture_account', 'Format d’image non supporté. Utilisez JPG, PNG ou WebP.');
                return $this->redirectToRoute('app_account');
            }

            $firstName = $user->getFirstnameUser();
            $lastName = $user->getLastnameUser();
            $baseSlug = $slugger->slug("photo-de-profil-$firstName-$lastName")->lower();

            $uploadDir = $this->getParameter('profile_pictures_directory');
            $filename = $baseSlug . '.jpg';
            $counter = 1;
            while (file_exists($uploadDir . '/' . $filename)) {
                $filename = $baseSlug . '-' . $counter . '.jpg';
                $counter++;
            }

            $fullPath = $uploadDir . '/' . $filename;

            $currentPicturePath = $user->getPictureUser();
            $defaultPicture = 'images/general/images-des-formulaires/logo-la-cine-network-retrecit.png';

            if ($currentPicturePath && $currentPicturePath !== $defaultPicture) {
                $absolutePath = $this->getParameter('kernel.project_dir') . '/public/' . $currentPicturePath;
                if (file_exists($absolutePath)) {
                    @unlink($absolutePath);
                }
            }

            imagejpeg($src, $fullPath, 85);
            imagedestroy($src);

            $user->setPictureUser($filename);
            $em->flush();

            $this->addFlash('success_picture_account', 'Votre photo de profil a été mise à jour.');
        }

        return $this->redirectToRoute('app_account');
    }

    #[Route('/mon-compte/change-password', name: 'app_account_change_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        Security $security,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        CsrfTokenManagerInterface $csrfTokenManager
    ): RedirectResponse {
        $user = $security->getUser();

        /** @var \App\Entity\User $user */
        if (!$user) {
            $this->addFlash('danger_change_password', 'Utilisateur non connecté.');
            return $this->redirectToRoute('app_account');
        }

        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('change_password', $submittedToken))) {
            $this->addFlash('danger_change_password', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_account');
        }

        $currentPassword = $request->request->get('current_password');
        $newPassword = $request->request->get('new_password');
        $confirmPassword = $request->request->get('confirm_password');

        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            $this->addFlash('danger_change_password', 'Le mot de passe actuel est incorrect.');
            return $this->redirectToRoute('app_account');
        }

        if ($newPassword !== $confirmPassword) {
            $this->addFlash('danger_change_password', 'Les mots de passe ne correspondent pas.');
            return $this->redirectToRoute('app_account');
        }

        if (strlen($newPassword) < 8) {
            $this->addFlash('danger_change_password', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
            return $this->redirectToRoute('app_account');
        }

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $em->flush();

        $this->addFlash('success_change_password', 'Votre mot de passe a été mis à jour.');
        return $this->redirectToRoute('app_account');
    }


    #[Route('/mon-compte/delete-account', name: 'app_account_delete_account', methods: ['POST'])]
    public function deleteAccount(
        Request $request,
        Security $security,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager
    ): RedirectResponse {
        $user = $security->getUser();

        /** @var \App\Entity\User $user */
        if (!$user) {
            $this->addFlash('danger_delete_account', 'Utilisateur non connecté.');
            return $this->redirectToRoute('app_account');
        }

        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete_account', $submittedToken))) {
            $this->addFlash('danger_delete_account', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_account');
        }

        if ($this->isGrantedAny([
            'ROLE_ADMIN',
            'ROLE_SUB_ADMIN',
            'ROLE_SUB_ADMIN_FORMATION',
            'ROLE_SUB_ADMIN_ANNOUNCEMENT',
            'ROLE_SUB_ADMIN_EVENT',
            'ROLE_SUB_ADMIN_BLOG',
            'ROLE_SUB_ADMIN_COMMENTARY',
            'ROLE_SUB_ADMIN_GESTION',
        ])) {
            $this->addFlash('danger_delete_account', 'Un compte administrateur ne peut pas être supprimé.');
            return $this->redirectToRoute('app_account');
        }

        $confirm = $request->request->get('confirm_delete');
        if ($confirm !== 'DELETE') {
            $this->addFlash('danger_delete_account', 'Vous devez taper "DELETE" pour confirmer la suppression.');
            return $this->redirectToRoute('app_account');
        }

        $defaultPicture = 'images/images-necessaire-au-site/logo-cine-network-fond-rouge.png';
        $picturePath = $user->getPictureUser();
        if ($picturePath && $picturePath !== $defaultPicture) {
            $fullPath = $this->getParameter('kernel.project_dir') . '/public/' . $picturePath;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        $this->container->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        $em->remove($user);
        $em->flush();

        $this->addFlash('success_delete_account', 'Votre compte a bien été supprimé.');
        return $this->redirectToRoute('app_index');
    }

    #[Route('/mon-compte/update-info', name: 'app_account_update_info', methods: ['POST'])]
    public function updateInfo(
        Request $request,
        Security $security,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager
    ): RedirectResponse {
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('danger_update_info_account', 'Utilisateur non connecté.');
            return $this->redirectToRoute('app_account');
        }

        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('update_info', $submittedToken))) {
            $this->addFlash('danger_update_info_account', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_account');
        }

        /** @var \App\Entity\User $user */
        if ($request->request->has('firstnameUser')) {
            $firstname = $request->request->get('firstnameUser');
            if ($firstname !== null) {
                $user->setFirstnameUser($firstname);
            }
        }

        if ($request->request->has('lastnameUser')) {
            $lastname = $request->request->get('lastnameUser');
            if ($lastname !== null) {
                $user->setLastnameUser($lastname);
            }
        }

        if ($request->request->has('email')) {
            $email = $request->request->get('email');
            if ($email !== null) {
                $user->setEmail($email);
            }
        }

        if ($request->request->has('telephoneUser')) {
            $telephone = $request->request->get('telephoneUser');
            if ($telephone !== null) {
                $user->setTelephoneUser($telephone);
            }
        }

        $fieldOfEvolution = $request->request->get('fieldOfEvolutionUser');
        $intermittent = $request->request->get('intermittentUser');

        /** @var UploadedFile|null $cvFile */
        $cvFile = $request->files->get('curriculumUser');

        if ($cvFile) {
            $slugger = new AsciiSlugger();
            $prenom = $slugger->slug($user->getFirstnameUser());
            $nom = $slugger->slug($user->getLastnameUser());
            $baseName = strtolower("cv-$prenom-$nom");
            $ext = $cvFile->guessExtension();
            $uploadDir = $this->getParameter('cv_user_directory');

            $filename = "$baseName.$ext";
            $counter = 1;
            while (file_exists("$uploadDir/$filename")) {
                $filename = "$baseName-$counter.$ext";
                $counter++;
            }

            try {
                $cvFile->move($uploadDir, $filename);
                $user->setCurriculumUser($filename);
            } catch (\Exception $e) {
                $this->addFlash('danger_update_info_account', 'Erreur lors du téléversement du CV.');
            }
        }

        $user->setFieldOfEvolutionUser($fieldOfEvolution);
        $user->setIntermittentUser($intermittent);

        $em->flush();

        $this->addFlash('success_update_info_account', 'Vos informations personnelles ont été mises à jour.');
        return $this->redirectToRoute('app_account');
    }

    #[Route('/mon-compte/annonce/{id}/modifier', name: 'app_account_edit_announcement', methods: ['POST'])]
    public function editAnnouncement(
        int $id,
        Request $request,
        Security $security,
        EntityManagerInterface $em,
        AnnouncementRepository $announcementRepository,
        SubCategoryAnnouncementRepository $subCategoryAnnouncementRepository,
        CsrfTokenManagerInterface $csrfTokenManager
    ): RedirectResponse {
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('danger_announcement_account', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_account');
        }

        $announcement = $announcementRepository->find($id);

        if (!$announcement || $announcement->getUser() !== $user) {
            $this->addFlash('danger_announcement_account', 'Annonce introuvable ou accès non autorisé.');
            return $this->redirectToRoute('app_account');
        }

        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('edit_announcement_' . $id, $submittedToken))) {
            $this->addFlash('danger_announcement_account', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_account');
        }

        $availability = new \DateTime($request->request->get('availability-account'), new \DateTimeZone('Europe/Paris'));
        $availability->setTime(0, 0);
        $expiration = new \DateTime($request->request->get('ad-expiration-account'), new \DateTimeZone('Europe/Paris'));
        $expiration->setTime(0, 0);

        if ($expiration <= $availability) {
            $this->addFlash('danger_announcement_account', 'La date de fin doit être postérieure à la date de début.');
            return $this->redirectToRoute('app_account', ['_fragment' => 'publier-annonce-mon-compte']);
        }

        $announcement->setTypeAnnouncement($request->request->get('ad-type-account'));
        $announcement->setDepartmentAnnouncement($request->request->get('department-account'));
        $announcement->setCityAnnouncement(trim($request->request->get('city-account')));
        $announcement->setTextAnnouncement(trim($request->request->get('short-description-account')));
        $announcement->setAvailabilityAnnouncement($availability);
        $announcement->setExpiryAnnouncement($expiration);
        $announcement->setLinkAnnouncement(trim($request->request->get('useful-link-account')));
        $announcement->setRemuneration($request->request->get('is-paid-account') === 'oui');

        $subCategoryId = $request->request->get('job-search-account');
        $subCategory = $subCategoryAnnouncementRepository->find($subCategoryId);
        if ($subCategory) {
            $announcement->setSubCategoryAnnouncement($subCategory);
        }

        $em->flush();

        $this->addFlash('success_annoncement_account', 'Votre annonce a bien été modifiée.');
        return $this->redirectToRoute('app_account', ['_fragment' => 'publier-annonce-mon-compte']);
    }

    #[Route('/mon-compte/annonce/{id}/supprimer', name: 'app_account_delete_announcement', methods: ['POST'])]
    public function deleteAnnouncement(
        int $id,
        Security $security,
        AnnouncementRepository $announcementRepository,
        EntityManagerInterface $em,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager
    ): RedirectResponse {
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('danger_announcement_account', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_account');
        }

        $announcement = $announcementRepository->find($id);

        if (!$announcement || $announcement->getUser() !== $user) {
            $this->addFlash('danger_announcement_account', 'Annonce introuvable ou accès non autorisé.');
            return $this->redirectToRoute('app_account');
        }

        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete_announcement_' . $id, $submittedToken))) {
            $this->addFlash('danger_announcement_account', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_account');
        }

        $em->remove($announcement);
        $em->flush();

        $this->addFlash('success_annoncement_account', 'Annonce supprimée avec succès.');
        return $this->redirectToRoute('app_account', ['_fragment' => 'publier-annonce-mon-compte']);
    }

    #[Route('/mon-compte/event/{id}/unregister', name: 'account_event_unregister', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function unregisterFromAccount(
        Event $event,
        EntityManagerInterface $entityManager,
        UserEventRepository $userEventRepository,
        MailerInterface $mailer,
        GeneralCineNetworkRepository $cineNetworkRepository,
    ): RedirectResponse {
        $user = $this->getUser();
        /** @var \App\Entity\User $user */

        $existingRegistration = $userEventRepository->findOneBy([
            'user' => $user,
            'event' => $event,
        ]);

        if (!$existingRegistration) {
            $this->addFlash('warning_event_account', 'Vous n\'êtes pas inscrit à cet événement.');
            return $this->redirectToRoute('app_account', ['_fragment' => 'prochain-evenement-mon-compte']);
        }

        $entityManager->remove($existingRegistration);
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

        $this->addFlash('success_event_account', 'Votre inscription à l\'événement a été annulée.');

        return $this->redirectToRoute('app_account', ['_fragment' => 'prochain-evenement-mon-compte']);
    }

    #[Route('/commentaire/{id}/supprimer', name: 'commentary_delete', methods: ['POST'])]
    public function deleteCommentary(
        int $id,
        CommentaryRepository $commentaryRepository,
        EntityManagerInterface $em,
        Security $security,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager
    ): RedirectResponse {
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('danger_comment_account', 'Vous devez être connecté pour effectuer cette action.');
            return $this->redirectToRoute('app_login');
        }

        $commentary = $commentaryRepository->find($id);

        if (!$commentary || $commentary->getUser() !== $user) {
            $this->addFlash('danger_comment_account', 'Commentaire introuvable ou non autorisé.');
            return $this->redirectToRoute('app_account', ['_fragment' => 'vos-commentaires']);
        }

        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete_commentary_' . $id, $submittedToken))) {
            $this->addFlash('danger_comment_account', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_account', ['_fragment' => 'vos-commentaires']);
        }

        $em->remove($commentary);
        $em->flush();

        $this->addFlash('success_comment_account', 'Commentaire supprimé avec succès.');
        return $this->redirectToRoute('app_account', ['_fragment' => 'vos-commentaires']);
    }
}
