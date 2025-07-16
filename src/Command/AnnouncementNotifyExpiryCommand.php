<?php

namespace App\Command;

use App\Entity\Announcement;
use App\Repository\GeneralCineNetworkRepository;
use App\Repository\AnnouncementRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

#[AsCommand(
    name: 'announcement:notify-expiry',
    description: 'Envoie un email aux utilisateurs dont les annonces expirent dans 48h.'
)]
class AnnouncementNotifyExpiryCommand extends Command
{
    public function __construct(
        private AnnouncementRepository $announcementRepository,
        private GeneralCineNetworkRepository $generalCineNetworkRepository,
        private MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $general = $this->generalCineNetworkRepository->findOneBy([]);
        $emailFrom = $general?->getEmailCompany() ?? 'no-reply@lacinenetwork.fr';

        $targetDate = (new \DateTimeImmutable())->modify('+2 days')->setTime(0, 0);
        $announcements = $this->announcementRepository->findByExpiryDate($targetDate);
        $notifiedCount = 0;

        foreach ($announcements as $announcement) {
            $user = $announcement->getUser();
            $expiry = $announcement->getExpiryAnnouncement();

            if (!$user || !$user->getEmail() || !$expiry instanceof \DateTimeInterface) {
                $output->writeln(sprintf(
                    '⚠️ Annonce ID %d ignorée (utilisateur ou email manquant).',
                    $announcement->getId()
                ));
                continue;
            }

            try {
                $email = (new Email())
                    ->from(new Address($emailFrom, 'La Ciné Network'))
                    ->to($user->getEmail())
                    ->subject('📅 Votre annonce expire dans 48h')
                    ->html(sprintf(
                        <<<'HTML'
                        <html>
                        <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px;">
                            <table width="100%%" style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                <tr>
                                    <td style="background-color: #000000; padding: 20px; border-radius: 8px 8px 0 0; text-align: center;">
                                        <h1 style="color: #feda00; margin: 0;">La Ciné Network</h1>
                                        <p style="color: #ffffff; font-size: 14px; margin: 5px 0 0;">Notification de fin d’annonce</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 30px;">
                                        <p style="font-size: 16px; color: #333333;">
                                            Bonjour %s,
                                        </p>
                                        <p style="font-size: 15px; color: #333333; line-height: 1.5;">
                                            Votre annonce arrive à expiration dans <strong>48 heures</strong>, soit le <strong style="color: #cc0000;">%s</strong>.<br><br>
                                            Si vous souhaitez prolonger sa visibilité, vous pouvez modifier la date de fin directement depuis votre <a href="https://lacinenetwork.fr/mon-compte" style="color: #007BFF; text-decoration: none;">espace personnel</a>.
                                        </p>
                                        <p style="font-size: 14px; color: #666666; font-style: italic;">
                                            Si vous n’effectuez aucune action, l’annonce sera automatiquement désactivée.
                                        </p>
                                        <div style="text-align: center; margin-top: 30px;">
                                            <a href="https://www.lacinenetwork.com/mon-compte" style="display: inline-block; background-color: #feda00; color: #000000; padding: 12px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;">
                                                Gérer mon annonce
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
                $notifiedCount++;

                $output->writeln(sprintf(
                    '✅ Notification envoyée à %s pour l’annonce ID %d.',
                    $user->getEmail(),
                    $announcement->getId()
                ));
            } catch (\Throwable $e) {
                $output->writeln(sprintf(
                    '❌ Erreur lors de l’envoi à %s : %s',
                    $user->getEmail(),
                    $e->getMessage()
                ));
            }
        }

        $output->writeln("\n🔔 Total des notifications envoyées : $notifiedCount");

        return Command::SUCCESS;
    }
}
