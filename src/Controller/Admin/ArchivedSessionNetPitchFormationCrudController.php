<?php

namespace App\Controller\Admin;

use App\Entity\ArchivedSessionNetPitchFormation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use App\Controller\Admin\SessionNetPitchFormationCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use App\Service\CsvExporterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArchivedSessionNetPitchFormationCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;
    private EntityManagerInterface $entityManager;

    public function __construct(AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $entityManager)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return ArchivedSessionNetPitchFormation::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('sessionNetPitchFormation', 'Session d’origine'))
            ->add(DateTimeFilter::new('archivedAt', 'Date d’archivage'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportCsvAction = Action::new('exportArchivedSessionsCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_archived_sessions')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary');

        return $actions
            ->disable(Action::NEW, Action::EDIT)
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
            ->setEntityLabelInSingular('Session archivée')
            ->setEntityLabelInPlural('Sessions archivées')
            ->setDefaultSort(['archivedAt' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('sessionNetPitchFormation', 'Session d’origine')->setCrudController(SessionNetPitchFormationCrudController::class),
            DateTimeField::new('archivedAt', 'Date d’archivage'),

            ArrayField::new('validatedRegistrations', 'Inscriptions archivées')
                ->setTemplatePath('admin/archived_registrations.html.twig'),

            Field::new('sessionSpeakers', 'Formateurs')
                ->setVirtual(true)
                ->formatValue(function ($_, $entity) {
                    return $entity->getSessionSpeakers();
                })
                ->setTemplatePath('admin/speaker-session-archived.html.twig')
                ->setHelp('Formateurs extraits de la session liée.')
        ];
    }

    #[Route('/admin/export-archived-sessions', name: 'export_archived_sessions')]
    public function exportArchivedSessions(CsvExporterService $csvExporter): Response
    {
        $sessions = $this->entityManager->getRepository(ArchivedSessionNetPitchFormation::class)->findAll();

        $data = [];

        foreach ($sessions as $session) {
            $original = $session->getSessionNetPitchFormation();

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

            $speakers = $session->getSessionSpeakers() ?? [];
            $speakerString = count($speakers) > 0 ? implode("\n", array_map(fn($s) => (string) $s, $speakers)) : 'Aucun';

            $data[] = [
                $original?->getNetPitchFormation()?->getTitleNetPitchFormation() ?? 'N/A',
                $original?->getLocation()?->__toString() ?? 'N/A',
                $original?->isRemoteSession() ? 'À distance' : 'Présentiel',
                $original?->getStartDateSessionNetPitchFormation()?->format('d/m/Y') ?? 'N/A',
                $original?->getEndDateSessionNetPitchFormation()?->format('d/m/Y') ?? 'N/A',
                $session->getArchivedAt()?->format('d/m/Y') ?? 'N/A',
                $studentString,
                $speakerString,
                $original?->isDraft() ? 'Brouillon' : 'Publiée',
            ];
        }

        $headers = [
            'Formation',
            'Lieu',
            'Date de début',
            'Date de fin',
            'Date d\'archivage',
            'Étudiants archivés',
            'Formateurs',
        ];

        return $csvExporter->export($data, $headers, 'sessions_archivées.csv');
    }
}
