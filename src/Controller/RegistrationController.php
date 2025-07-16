<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\GeneralCineNetworkRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, SluggerInterface $slugger, GeneralCineNetworkRepository $generalCineNetworkRepository): Response
    {
        $generalCineNetwork = $generalCineNetworkRepository->findOneBy([]);
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $user->setRoles(['ROLE_USER_NETWORK']);

            $user->setConditionValidated(new \DateTimeImmutable('Europe/Paris'));

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('pictureUser')->getData();
            if ($imageFile) {
                $prenom = $slugger->slug($user->getFirstnameUser());
                $nom = $slugger->slug($user->getLastnameUser());
                $email = $slugger->slug($user->getEmail());

                $extension = $imageFile->guessExtension();
                $newFilename = strtolower("{$prenom}-{$nom}-{$email}." . $extension);

                try {
                    $imageFile->move(
                        $this->getParameter('profile_pictures_directory'),
                        $newFilename
                    );
                    $user->setPictureUser($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                }
            }

            $cvFile = $form->get('curriculumUser')->getData();
            if ($cvFile) {
                $prenom = $slugger->slug($user->getFirstnameUser());
                $nom = $slugger->slug($user->getLastnameUser());
                $extension = $cvFile->guessExtension() ?? 'pdf';

                $baseFilename = strtolower("{$prenom}-{$nom}-cv");
                $filename = $baseFilename . '.' . $extension;
                $targetDir = $this->getParameter('cv_user_directory');
                $finalPath = $targetDir . '/' . $filename;

                $i = 1;
                while (file_exists($finalPath)) {
                    $filename = "{$baseFilename}-{$i}.{$extension}";
                    $finalPath = $targetDir . '/' . $filename;
                    $i++;
                }

                try {
                    $cvFile->move($targetDir, $filename);
                    $user->setCurriculumUser($filename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du téléversement du CV.');
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $cineNetwork = $generalCineNetworkRepository->findOneBy([]);
            $emailFrom = $cineNetwork ? $cineNetwork->getEmailCompany() : 'noreply@fallback.com';
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address($emailFrom, 'La Ciné Network'))
                    ->to((string) $user->getEmail())
                    ->subject('Merci de confirmer votre adresse e-mail')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('success', 'Votre compte a bien été créé. Un email de confirmation vous a été envoyé.');
            return $this->redirectToRoute('app_register');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'generalCineNetwork' => $generalCineNetwork,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('succès', 'Votre adresse e-mail a été vérifiée');

        return $this->redirectToRoute('app_login');
    }
}
