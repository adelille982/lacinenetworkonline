<?php

namespace App\Controller\Admin;

use App\Entity\ArchivedSessionNetPitchFormation;
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
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SessionNetPitchFormationCrudController extends AbstractCrudController
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
            ->linkToRoute('export_sessions_upcoming_csv')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary');

        return $actions
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
            ->setEntityLabelInSingular('Session à venir')
            ->setEntityLabelInPlural('Sessions à venir')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, $fields, $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $today = new \DateTime();

        return $qb
            ->andWhere('entity.startDateSessionNetPitchFormation > :today')
            ->andWhere('entity.draft = false')
            ->setParameter('today', $today);
    }

    public function configureFields(string $pageName): iterable
    {

        $imageDirectory = $this->projectDir . '/public/images/formation/image-session';
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
            BooleanField::new('remoteSession', 'À distance')->onlyOnIndex(),
            AssociationField::new('location', 'Lieu')->onlyOnIndex(),
            DateTimeField::new('startDateSessionNetPitchFormation', 'Début')->onlyOnIndex(),
            DateTimeField::new('endDateSessionNetPitchFormation', 'Fin')->onlyOnIndex(),
            AssociationField::new('netPitchFormation', 'Formation')->onlyOnIndex(),
            ArrayField::new('validatedRegistrations', 'Étudiants validés')
                ->setTemplatePath('admin/current_validated_registrations.html.twig')
                ->onlyOnIndex(),
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
            BooleanField::new('draft', 'Brouillon'),
        ];

        $formFields = [
            FormField::addTab('Information générale de la session')->setIcon('fa fa-calendar'),
            DateTimeField::new('startDateSessionNetPitchFormation', 'Date de début')
                ->setHelp('Indiquer la date de démarrage de la session de formation. Les sessions passées sont automatiquement archivées.'),
            DateTimeField::new('endDateSessionNetPitchFormation', 'Date de fin')
                ->setHelp('Indiquer la date de fin de la session de formation. Les sessions passées sont automatiquement archivées.'),
            AssociationField::new('location', 'Lieu')
                ->setFormTypeOptions([
                    'query_builder' => function (\App\Repository\LocationRepository $repo) {
                        return $repo->createQueryBuilder('l')
                            ->where('l.typeLocation = :type')
                            ->setParameter('type', 'Formation');
                    },
                ])
                ->setHelp('Choisir un lieu de type "Formation". Les autres types de lieux sont exclus automatiquement.<br>
                S\'il s\'agit d\'une session à distance, laisser vide et cocher "Session à distance".'),
            BooleanField::new('remoteSession', 'Session à distance')
                ->setHelp('Indiquer si la session se déroule entièrement à distance (visioconférence).'),
            IntegerField::new('maxNumberRegistrationSessionNetPitchFormation', 'Participants Max')
                ->setHelp('Nombre de participants recommandé pour cette session. Il sert uniquement à afficher le nombre de places disponibles, mais ne limite pas techniquement les inscriptions.'),

            FormField::addTab('Image de la session')->setIcon('fa fa-calendar'),
            ChoiceField::new('imgSessionNetPitchFormation', 'sélectionner une image pour la session')
                ->setChoices(array_combine($images, $images))
                ->setRequired(true)
                ->setFormTypeOption('empty_data', '')
                ->onlyOnForms()
                ->setHelp('<small>
        Sélectionnez une image déjà présente dans le dossier <code>/formation/image-session/</code>.<br>
        Cette image apparaîtra sur la section qui présente les futurs sessions à venir<br>
        <strong>Format conseillé :</strong> JPG ou WebP, format paysage.
    </small>'),
            ImageField::new('imgSessionNetPitchFormation', 'Image session')
                ->setBasePath('/images/formation/image-session/')
                ->setUploadDir('public/images/formation/image-session/')
                ->onlyOnIndex(),

            ImageField::new('imgSessionNetPitchFormation', 'Image session')
                ->setBasePath('/images/formation/image-session/')
                ->setUploadDir('public/images/formation/image-session/')
                ->onlyOnDetail(),

            FormField::addTab('Formation & formateurs associées')->setIcon('fa fa-link'),
            AssociationField::new('netPitchFormation', 'Formation associée')
                ->setHelp('Sélectionner la formation principale liée à cette session. Elle détermine le programme général.'),
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
                ->setHelp('Ajouter uniquement des intervenants de type "Formateur" ayant le statut "Validé". Ils seront visibles sur la page formation publique.'),

            FormField::addTab('Brouillon')->setIcon('fas fa-pencil-alt'),
            BooleanField::new('draft', 'Brouillon')
                ->setHelp('Si activé, la session ne sera pas visible publiquement. Utile pour préparer ou relire les contenus avant mise en ligne.'),
        ];

        return $pageName === 'index' ? $commonFields : $formFields;
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if (!$entityInstance instanceof SessionNetPitchFormation) {
            parent::persistEntity($em, $entityInstance);
            return;
        }

        $now = new \DateTimeImmutable();
        $start = $entityInstance->getStartDateSessionNetPitchFormation();
        $end = $entityInstance->getEndDateSessionNetPitchFormation();

        if ($start < $now && $end < $now) {
            $archived = new ArchivedSessionNetPitchFormation();
            $archived->setSessionNetPitchFormation($entityInstance);
            $archived->setArchivedAt($now->modify('+1 day'));

            $em->persist($archived);
        }

        parent::persistEntity($em, $entityInstance);
    }

    #[Route('/admin/export-sessions-upcoming-csv', name: 'export_sessions_upcoming_csv')]
    public function exportCsv(\App\Service\CsvExporterService $csvExporter, EntityManagerInterface $em): StreamedResponse
    {
        $today = new \DateTime();

        $sessions = $em->createQueryBuilder()
            ->select('s')
            ->from(SessionNetPitchFormation::class, 's')
            ->where('s.startDateSessionNetPitchFormation > :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();

        $data = [];

        foreach ($sessions as $session) {
            $speakers = $session->getSpeakers()?->map(fn($s) => (string) $s)->toArray() ?? [];
            $speakerString = count($speakers) > 0 ? implode("\n", $speakers) : 'Aucun';

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

            $data[] = [
                $session->getNetPitchFormation()?->getTitleNetPitchFormation() ?? 'N/A',
                $session->getLocation()?->__toString() ?? 'N/A',
                $session->getStartDateSessionNetPitchFormation()?->format('d/m/Y') ?? 'N/A',
                $session->getEndDateSessionNetPitchFormation()?->format('d/m/Y') ?? 'N/A',
                $session->isRemoteSession() ? 'Oui' : 'Non',
                $session->getMaxNumberRegistrationSessionNetPitchFormation(),
                $studentString,
                $speakerString,
                $session->isDraft() ? 'Oui' : 'Non',
            ];
        }

        $headers = [
            'Formation',
            'Lieu',
            'Date de début',
            'Date de fin',
            'À distance',
            'Participants Max',
            'Étudiants validés',
            'Formateurs',
            'Brouillon',
        ];

        return $csvExporter->export($data, $headers, 'sessions_a_venir.csv');
    }
}
