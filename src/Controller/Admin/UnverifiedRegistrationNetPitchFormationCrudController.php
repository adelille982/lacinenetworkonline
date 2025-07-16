<?php

namespace App\Controller\Admin;

use App\Entity\RegistrationNetPitchFormation;
use App\Service\CsvExporterService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use App\Repository\GeneralCineNetworkRepository;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class UnverifiedRegistrationNetPitchFormationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RegistrationNetPitchFormation::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('firstnameRegistration', 'Prénom'))
            ->add(TextFilter::new('lastnameRegistration', 'Nom'))
            ->add(TextFilter::new('emailRegistration', 'Email'))
            ->add(TextFilter::new('telRegistration', 'Téléphone'))
            ->add(ChoiceFilter::new('afdas', 'Éligible AFDAS')
                ->setChoices([
                    'Oui' => true,
                    'Non' => false,
                ]))
            ->add(TextFilter::new('professionalProjectRegistration', 'Projet professionnel'))
            ->add(TextFilter::new('cvRegistration', 'Nom du CV'))
            ->add(EntityFilter::new('sessionNetPitchFormation', 'Session liée'))
            ->add(ChoiceFilter::new('statutRegistration', 'Statut')->setChoices([
                'En cours' => 'En cours',
                'Validé' => 'Validé',
            ]))
            ->add(DateTimeFilter::new('createdAtRegistration', 'Date d’inscription'));
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Inscription en cours')
            ->setEntityLabelInPlural('Inscriptions en cours')
            ->setDefaultSort(['createdAtRegistration' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $validateAction = Action::new('validate', 'Valider l\'inscription')
            ->linkToCrudAction('validateRegistration')
            ->displayIf(static function ($entity) {
                return $entity->getStatutRegistration() === 'En cours';
            })
            ->setIcon('fa fa-check')
            ->addCssClass('btn btn-success');

        $exportCsvAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_registration_in_progress_csv')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary');

        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $exportCsvAction)
            ->add(Crud::PAGE_INDEX, $validateAction);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, $fields, $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $now = new \DateTime();

        return $qb
            ->join('entity.sessionNetPitchFormation', 'session')
            ->andWhere('entity.statutRegistration = :status')
            ->andWhere('session.startDateSessionNetPitchFormation > :now')
            ->setParameter('status', 'En cours')
            ->setParameter('now', $now);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('cvRegistration', 'CV')
                ->formatValue(function ($value) {
                    if (!$value) {
                        return '<span style="color: gray;">Aucun fichier</span>';
                    }

                    $filename = basename($value);

                    return sprintf(
                        '<a href="/images/cv/cv-inscriptions-formations/%s" target="_blank" class="btn btn-sm btn-primary">📄 Voir le CV</a>',
                        htmlspecialchars($filename)
                    );
                })
                ->renderAsHtml(),
            TextField::new('firstnameRegistration', 'Prénom'),
            TextField::new('lastnameRegistration', 'Nom'),
            TextField::new('emailRegistration', 'Email'),
            TextField::new('telRegistration', 'Téléphone'),
            TextEditorField::new('professionalProjectRegistration', 'Projet professionnel'),

            AssociationField::new('sessionNetPitchFormation', 'Session')->onlyOnIndex(),

            BooleanField::new('afdas', 'Éligible AFDAS'),

            ChoiceField::new('statutRegistration', 'Statut')
                ->setChoices([
                    'En cours' => 'En cours',
                    'Validé' => 'Validé',
                ]),

            DateTimeField::new('createdAtRegistration', 'Date d’inscription'),
        ];
    }

    public function validateRegistration(
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator,
        MailerInterface $mailer,
        Environment $twig,
        GeneralCineNetworkRepository $generalCineNetworkRepository,
    ): RedirectResponse {
        $request = $requestStack->getCurrentRequest();
        $id = $request->query->get('entityId');

        $registration = $entityManager->getRepository(RegistrationNetPitchFormation::class)->find($id);

        if ($registration) {
            $registration->setStatutRegistration('Validé');
            $entityManager->flush();

            $emailContent = $twig->render('admin/validated-registration.html.twig', [
                'registration' => $registration,
            ]);

            $cineNetwork = $generalCineNetworkRepository->findOneBy([]);
            $emailFrom = $cineNetwork ? $cineNetwork->getEmailCompany() : 'noreply@fallback.com';

            $email = (new Email())
                ->from($emailFrom)
                ->to($registration->getEmailRegistration())
                ->subject('Votre inscription à la formation est validée')
                ->html($emailContent);

            $mailer->send($email);

            $this->addFlash('success', 'Inscription validée avec succès et email envoyé.');
        } else {
            $this->addFlash('danger', 'Inscription introuvable.');
        }

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction('index')
            ->generateUrl();

        return new RedirectResponse($url);
    }

    #[Route('/export-registration-in-progress-csv', name: 'export_registration_in_progress_csv')]
    public function exportCsv(CsvExporterService $csvExporter, EntityManagerInterface $entityManager): Response
    {
        $now = new \DateTime();

        $registrations = $entityManager->createQueryBuilder()
            ->select('r')
            ->from(RegistrationNetPitchFormation::class, 'r')
            ->join('r.sessionNetPitchFormation', 's')
            ->where('r.statutRegistration = :status')
            ->andWhere('s.startDateSessionNetPitchFormation > :now')
            ->setParameter('status', 'En cours')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        $data = [];

        foreach ($registrations as $registration) {
            $data[] = [
                $registration->getFirstnameRegistration(),
                $registration->getLastnameRegistration(),
                $registration->getEmailRegistration(),
                $registration->getTelRegistration(),
                $registration->isAfdas() ? 'Oui' : 'Non',
                $registration->getSessionNetPitchFormation()?->getNetPitchFormation()?->getTitleNetPitchFormation() ?? 'N/A',
                $registration->getSessionNetPitchFormation()?->getStartDateSessionNetPitchFormation()?->format('d/m/Y') ?? 'N/A',
                $registration->getSessionNetPitchFormation()?->getEndDateSessionNetPitchFormation()?->format('d/m/Y') ?? 'N/A',
                $registration->getCreatedAtRegistration()?->format('Y-m-d H:i:s'),
                $registration->getProfessionalProjectRegistration(),
            ];
        }

        $headers = [
            'Prénom',
            'Nom',
            'Email',
            'Téléphone',
            'Éligible AFDAS',
            'Formation',
            'Date de début',
            'Date de fin',
            'Date d\'inscription',
            'Nom du CV',
            'Projet professionnel',
        ];

        return $csvExporter->export($data, $headers, 'inscriptions_en_cours.csv');
    }
}
