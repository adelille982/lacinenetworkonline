<?php

namespace App\Command;

use App\Entity\Announcement;
use App\Repository\GeneralCineNetworkRepository;
use App\Repository\AnnouncementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'announcement:delete-expired',
    description: 'Supprime les annonces expirées depuis plus d’une heure et notifie les utilisateurs.'
)]
class AnnouncementDeleteExpiredCommand extends Command
{
    public function __construct(
        private AnnouncementRepository $announcementRepository,
        private GeneralCineNetworkRepository $generalCineNetworkRepository,
        private EntityManagerInterface $em,
        private MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $general = $this->generalCineNetworkRepository->findOneBy([]);
        $emailFrom = $general?->getEmailCompany() ?? 'no-reply@lacinenetwork.fr';

        $now = new \DateTimeImmutable();
        $threshold = $now->modify('-1 hour');

        $expiredAnnouncements = $this->announcementRepository->findAll();
        $deletedCount = 0;

        foreach ($expiredAnnouncements as $announcement) {
            $expiry = $announcement->getExpiryAnnouncement();
            $user = $announcement->getUser();

            if ($expiry instanceof \DateTimeInterface && $expiry < $threshold) {
                if ($user && $user->getEmail()) {
                    $email = (new Email())
                        ->from(new Address($emailFrom, 'La Ciné Network'))
                        ->to($user->getEmail())
                        ->subject('🗑 Votre annonce a expiré et a été supprimée')
                        ->html(sprintf(
                            <<<HTML
                            <html>
                            <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px;">
                                <table style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    <tr>
                                        <td style="background-color: #000000; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
                                            <h1 style="color: #feda00; margin: 0;">La Ciné Network</h1>
                                            <p style="color: #ffffff; font-size: 14px;">Annonce expirée supprimée</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 30px;">
                                            <p style="font-size: 16px; color: #333;">Bonjour %s,</p>
                                            <p style="font-size: 15px; color: #333; line-height: 1.5;">
                                                Votre annonce est arrivée à expiration le <strong style="color:#cc0000;">%s</strong> et a été automatiquement supprimée.
                                            </p>
                                            <p style="font-size: 14px; color: #666; font-style: italic;">
                                                Nous serions ravis de vous retrouver prochainement pour une nouvelle publication !
                                            </p>
                                            <div style="text-align: center; margin-top: 30px;">
                                                <a href="https://www.lacinenetwork.com/annonces" style="display: inline-block; background-color: #feda00; color: #000000; padding: 12px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;">
                                                    Publier une nouvelle annonce
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: #f9f9f9; text-align: center; padding: 20px; font-size: 12px; color: #999999; border-radius: 0 0 8px 8px;">
                                            © %d La Ciné Network — Ne répondez pas à cet email.
                                        </td>
                                    </tr>
                                </table>
                            </body>
                            </html>
                            HTML,
                            $user->getFirstnameUser(),
                            $expiry->format('d/m/Y'),
                            date('Y')
                        ));

                    $this->mailer->send($email);
                }

                $this->em->remove($announcement);

                $output->writeln(sprintf(
                    '🗑 Annonce ID %d supprimée et email envoyé à %s.',
                    $announcement->getId(),
                    $user?->getEmail() ?? 'Utilisateur inconnu'
                ));
                $deletedCount++;
            }
        }

        $this->em->flush();
        $output->writeln("\n✅ Total des annonces supprimées : $deletedCount");

        return Command::SUCCESS;
    }
}
