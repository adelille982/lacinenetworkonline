<?php

namespace App\Controller\Admin;

use App\Entity\UserEvent;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use App\Repository\GeneralCineNetworkRepository;

class UserEventCrudController extends AbstractCrudController
{

    private MailerInterface $mailer;
    private AdminUrlGenerator $adminUrlGenerator;
    private RequestStack $requestStack;
    private EntityManagerInterface $entityManager;

    public function __construct(
        MailerInterface $mailer,
        AdminUrlGenerator $adminUrlGenerator,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager
    ) {
        $this->mailer = $mailer;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return UserEvent::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('event')->setLabel('Événement'))
            ->add(EntityFilter::new('user')->setLabel('Utilisateur'))
            ->add(DateTimeFilter::new('registeredAt')->setLabel('Date d’inscription'));
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Inscriptions aux événements')
            ->setEntityLabelInSingular('Inscription à un événement')
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $cancelAction = Action::new('cancelRegistration', 'Annuler l’inscription', 'fa fa-times')
            ->linkToCrudAction('cancelRegistration')
            ->displayIf(fn($entity) => $entity instanceof \App\Entity\UserEvent)
            ->addCssClass('btn btn-danger')
            ->setHtmlAttributes([
                'onclick' => "return confirm('⚠️ Êtes-vous sûr de vouloir annuler cette inscription ?')"
            ]);

        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::EDIT)
            ->add(Action::INDEX, $cancelAction);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, $fields, $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $now = new \DateTime();

        return $qb
            ->join('entity.event', 'e')
            ->andWhere('e.dateEvent >= :now')
            ->setParameter('now', $now);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('user')
                ->setLabel('Utilisateur')
                ->setRequired(true)
                ->setSortable(true)
                ->formatValue(fn($value, $entity) => $entity->getUserFullName()),

            AssociationField::new('event')
                ->setLabel('Événement')
                ->formatValue(function ($value) {
                    if (!$value) return '—';
                    return sprintf(
                        '%s – %s',
                        $value->getTitleEvent(),
                        $value->getDateEvent()?->format('d/m/Y') ?? 'Date inconnue'
                    );
                }),

            DateTimeField::new('registeredAt')
                ->setLabel('Date d\'inscription')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->hideOnForm(),
        ];
    }

    #[Route('/admin/user-event/cancel', name: 'admin_user_event_cancel')]
    public function cancelRegistration(GeneralCineNetworkRepository $generalCineNetworkRepository): RedirectResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $id = $request->query->get('entityId');

        $userEvent = $this->entityManager->getRepository(UserEvent::class)->find($id);

        if (!$userEvent) {
            $this->addFlash('danger', 'Inscription introuvable.');
        } else {
            $user = $userEvent->getUser();
            $event = $userEvent->getEvent();

            $this->entityManager->remove($userEvent);
            $this->entityManager->flush();


            $cineNetwork = $generalCineNetworkRepository->findOneBy([]);
            $emailFrom = $cineNetwork ? $cineNetwork->getEmailCompany() : 'noreply@fallback.com';

            if ($user && $user->getEmail()) {
                $email = (new Email())
                    ->from($emailFrom)
                    ->to($user->getEmail())
                    ->subject('Votre inscription a été annulée')
                    ->html(sprintf(
                        <<<HTML
                    <html>
                    <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px;">
                        <table width="100%%" style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <tr>
                                <td style="background-color: #000000; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
                                    <h1 style="color: #feda00; margin: 0;">La Ciné Network</h1>
                                    <p style="color: #ffffff; font-size: 14px;">Annulation d’inscription</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 30px;">
                                    <p style="font-size: 16px; color: #333333;">Bonjour %s,</p>
                                    <p style="font-size: 15px; color: #333333; line-height: 1.5;">
                                        Nous vous informons que votre inscription à l’événement <strong>%s</strong>, prévu le <strong style="color: #cc0000;">%s</strong>, a été annulée par notre équipe.<br><br>
                                        Si vous pensez qu’il s’agit d’une erreur, vous pouvez vous réinscrire à tout moment depuis ce <a href="https://www.lacinenetwork.com/#inscription-evenement" style="color: #007BFF; text-decoration: none;">lien</a>.
                                    </p>
                                    <p style="font-size: 14px; color: #666666; font-style: italic;">
                                        Merci de votre compréhension.
                                    </p>
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
                        htmlspecialchars($user->getFirstnameUser()),
                        htmlspecialchars($event->getTitleEvent()),
                        $event->getDateEvent()?->format('d/m/Y') ?? 'prochaine date à confirmer',
                        date('Y')
                    ));
                $this->mailer->send($email);
            }

            $this->addFlash('warning', 'Inscription annulée.');
        }

        return new RedirectResponse($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction('index')
            ->generateUrl());
    }
}
