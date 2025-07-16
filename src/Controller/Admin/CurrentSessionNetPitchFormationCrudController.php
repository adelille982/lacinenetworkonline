<?php

namespace App\Controller\Admin;

use App\Entity\SessionNetPitchFormation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CsvExporterService;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;

class CurrentSessionNetPitchFormationCrudController extends AbstractCrudController
{

    private string $projectDir;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(ParameterBagInterface $params, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->projectDir = $params->get('kernel.project_dir');
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return SessionNetPitchFormation::class;
    }

    public function configureFilters(\EasyCorp\Bundle\EasyAdminBundle\Config\Filters $filters): \EasyCorp\Bundle\EasyAdminBundle\Config\Filters
    {
        return $filters
            ->add(DateTimeFilter::new('startDateSessionNetPitchFormation', 'Date de début'))
            ->add(DateTimeFilter::new('endDateSessionNetPitchFormation', 'Date de fin'))
            ->add(EntityFilter::new('netPitchFormation', 'Formation associée'))
            ->add(EntityFilter::new('location', 'Lieu de la session'))
            ->add(BooleanFilter::new('remoteSession', 'À distance'))
            ->add(BooleanFilter::new('draft', 'Brouillon'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportCsvAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_sessions_current_csv')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary');

        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $exportCsvAction)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setLabel('Fiche')
                    ->setIcon('fas fa-book-open')
                    ->addCssClass('btn btn-info');
            });
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Session en cours')
            ->setEntityLabelInPlural('Sessions en cours')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, $fields, $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $today = new \DateTime();
        return $qb
            ->andWhere('entity.startDateSessionNetPitchFormation <= :today')
            ->andWhere('entity.endDateSessionNetPitchFormation >= :today')
            ->setParameter('today', $today);
    }

    public function configureFields(string $pageName): iterable
    {

        $imageDirectory = $this->projectDir . '/public/images/formation/image-session/';
        $filesystem = new Filesystem();
        $images = [];

        if ($filesystem->exists($imageDirectory)) {
            $images = array_diff(scandir($imageDirectory), ['.', '..']);
        }

        $commonFields = [
            ImageField::new('imgSessionNetPitchFormation', 'Image session')
                ->setBasePath('/images/formation/image-session/')
                ->setUploadDir('public/images/formation/image-session/')
                ->onlyOnIndex(),
            ImageField::new('imgSessionNetPitchFormation', 'Image session')
                ->setBasePath('/images/formation/image-session/')
                ->setUploadDir('public/images/formation/image-session/')
                ->onlyOnDetail(),
            IntegerField::new('maxNumberRegistrationSessionNetPitchFormation', 'Participants Max')->onlyOnIndex(),
            BooleanField::new('remoteSession', 'À distance')->hideOnIndex(),
            AssociationField::new('location', 'Lieu')->onlyOnIndex(),
            DateTimeField::new('startDateSessionNetPitchFormation', 'Début')->onlyOnIndex(),
            DateTimeField::new('endDateSessionNetPitchFormation', 'Fin')->onlyOnIndex(),
            AssociationField::new('netPitchFormation', 'Formation')->onlyOnIndex(),
            ArrayField::new('validatedRegistrations', 'Étudiants présents')
                ->setTemplatePath('admin/current_validated_registrations.html.twig')
                ->onlyOnIndex(),

            AssociationField::new('speakers', 'Formateurs')
                ->onlyOnIndex()
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'query_builder' => function (\App\Repository\SpeakerRepository $repo) {
                        return $repo->createQueryBuilder('s')
                            ->andWhere('s.typeSpeaker = :type')
                            ->setParameter('type', 'Formateur');
                    },
                ])
                ->setSortable(false)
                ->formatValue(function ($value, $entity) {
                    if ($value instanceof \Doctrine\Common\Collections\Collection) {
                        return implode('<br>', $value->map(function ($speaker) {
                            $controller = match ($speaker->getTypeSpeaker()) {
                                'Externe' => \App\Controller\Admin\ProposalCrudController::class,
                                'Stagiaire' => \App\Controller\Admin\InternCrudController::class,
                                'Réalisateur' => \App\Controller\Admin\ProducerCrudController::class,
                                'Formateur' => \App\Controller\Admin\TrainerCrudController::class,
                                'Jury' => \App\Controller\Admin\JuryCrudController::class,
                                'Entreprise' => \App\Controller\Admin\CompanySpeakerCrudController::class,
                                default => null,
                            };

                            if (!$controller) return (string)$speaker;

                            $url = $this->adminUrlGenerator
                                ->setController($controller)
                                ->setAction('detail')
                                ->setEntityId($speaker->getId())
                                ->generateUrl();

                            return sprintf('<a href="%s">%s</a>', $url, (string)$speaker);
                        })->toArray());
                    }
                    return '';
                })
                ->renderAsHtml()
                ->setHelp('Ajouter uniquement des intervenants de type "Formateur" ayant le statut "Validé".'),
        ];

        $formFields = [
            FormField::addTab('Information générale de la session')->setIcon('fa fa-calendar'),
            DateTimeField::new('startDateSessionNePitchFormation', 'Date de début'),
            DateTimeField::new('endDateSessionNetPitchFormation', 'Date de fin'),
            AssociationField::new('location', 'Lieu')
                ->setFormTypeOptions([
                    'query_builder' => function (\App\Repository\LocationRepository $repo) {
                        return $repo->createQueryBuilder('l')
                            ->where('l.typeLocation = :type')
                            ->setParameter('type', 'Formation');
                    },
                ]),
            BooleanField::new('remoteSession', 'Session à distance')->hideOnIndex(),
            IntegerField::new('maxNumberRegistrationSessionNetPitchFormation', 'Participants Max'),

            FormField::addTab('Image de la session')->setIcon('fa fa-calendar'),
            ChoiceField::new('imgSessionNetPitchFormation', 'sélectionner une image pour la session')
                ->setChoices(array_combine($images, $images))
                ->setRequired(false)
                ->setFormTypeOption('empty_data', '')
                ->onlyOnForms(),
            ImageField::new('imgSessionNetPitchFormation', 'Image session')
                ->setBasePath('/images/formation/image-session/')
                ->setUploadDir('public/images/formation/image-session/')
                ->onlyOnIndex(),

            ImageField::new('imgSessionNetPitchFormation', 'Image session')
                ->setBasePath('/images/formation/image-session/')
                ->setUploadDir('public/images/formation/image-session/')
                ->onlyOnDetail(),

            FormField::addTab('Formation & intervenants associées')->setIcon('fa fa-link'),
            AssociationField::new('netPitchFormation', 'Formation associée'),
            AssociationField::new('speakers', 'Formateurs')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'query_builder' => function (\App\Repository\SpeakerRepository $repo) {
                        return $repo->createQueryBuilder('s')
                            ->andWhere('s.typeSpeaker = :type')
                            ->setParameter('type', 'Formateur');
                    },
                ])
                ->setSortable(false)
                ->formatValue(function ($value, $entity) {
                    if ($value instanceof \Doctrine\Common\Collections\Collection) {
                        return implode('<br>', $value->map(function ($speaker) {
                            $controller = match ($speaker->getTypeSpeaker()) {
                                'Externe' => \App\Controller\Admin\ProposalCrudController::class,
                                'Stagiaire' => \App\Controller\Admin\InternCrudController::class,
                                'Réalisateur' => \App\Controller\Admin\ProducerCrudController::class,
                                'Formateur' => \App\Controller\Admin\TrainerCrudController::class,
                                'Jury' => \App\Controller\Admin\JuryCrudController::class,
                                'Entreprise' => \App\Controller\Admin\CompanySpeakerCrudController::class,
                                default => null,
                            };

                            if (!$controller) return (string)$speaker;

                            $url = $this->adminUrlGenerator
                                ->setController($controller)
                                ->setAction('detail')
                                ->setEntityId($speaker->getId())
                                ->generateUrl();

                            return sprintf('<a href="%s">%s</a>', $url, (string)$speaker);
                        })->toArray());
                    }
                    return '';
                })
                ->renderAsHtml()
                ->setHelp('Ajouter uniquement des intervenants de type "Formateur" ayant le statut "Validé".'),
        ];

        return $pageName === 'index' ? $commonFields : $formFields;
    }

    #[Route('/admin/export-sessions-current-csv', name: 'export_sessions_current_csv')]
    public function exportCsv(CsvExporterService $csvExporter, EntityManagerInterface $em): Response
    {
        $today = new \DateTime();

        $sessions = $em->createQueryBuilder()
            ->select('s')
            ->from(SessionNetPitchFormation::class, 's')
            ->where('s.startDateSessionNetPitchFormation <= :today')
            ->andWhere('s.endDateSessionNetPitchFormation >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();

        $data = [];

        foreach ($sessions as $session) {
            $registrations = $session->getValidatedRegistrations();
            $studentInfo = [];

            foreach ($registrations as $reg) {
                $studentInfo[] = sprintf(
                    "%s %s\n%s\n%s\n%s",
                    $reg->getFirstnameRegistration(),
                    $reg->getLastnameRegistration(),
                    $reg->getEmailRegistration(),
                    $reg->getTelRegistration(),
                    $reg->getStatutRegistration()
                );
            }

            $studentString = count($studentInfo) > 0 ? implode("\n\n", $studentInfo) : 'Aucun';

            $speakers = $session->getSpeakers()?->map(fn($s) => (string) $s)->toArray() ?? [];
            $speakerString = count($speakers) > 0 ? implode("\n", $speakers) : 'Aucun';

            $data[] = [
                $session->getNetPitchFormation()?->getTitleNetPitchFormation() ?? 'N/A',
                $session->getLocation()?->__toString() ?? 'N/A',
                $session->getStartDateSessionNetPitchFormation()?->format('d/m/Y') ?? 'N/A',
                $session->getEndDateSessionNetPitchFormation()?->format('d/m/Y') ?? 'N/A',
                $session->isRemoteSession() ? 'Oui' : 'Non',
                $session->getMaxNumberRegistrationSessionNetPitchFormation(),
                $studentString,
                $speakerString,
            ];
        }

        $headers = [
            'Formation',
            'Lieu',
            'Date de début',
            'Date de fin',
            'À distance',
            'Participants Max',
            'Étudiants présents',
            'Formateurs',
        ];

        return $csvExporter->export($data, $headers, 'sessions_en_cours.csv');
    }
}
