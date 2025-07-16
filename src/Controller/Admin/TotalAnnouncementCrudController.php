<?php

namespace App\Controller\Admin;

use App\Entity\Announcement;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CsvExporterService;
use Doctrine\Persistence\ManagerRegistry;

class TotalAnnouncementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Announcement::class;
    }

    public function configureActions(Actions $actions): Actions
    {

        $exportCsvAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_announcements_csv')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary')
            ->setIcon('fa fa-download');

        return $actions
            ->add(Crud::PAGE_INDEX, $exportCsvAction)
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
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
            ->setEntityLabelInPlural('Toutes les annonces')
            ->setEntityLabelInSingular('Annonce')
            ->setDefaultSort(['createdAtAnnouncement' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('typeAnnouncement', 'Type d\'annonce'))
            ->add(TextFilter::new('departmentAnnouncement', 'Département'))
            ->add(TextFilter::new('cityAnnouncement', 'Ville'))
            ->add(TextFilter::new('linkAnnouncement', 'Email de contact'))
            ->add(BooleanFilter::new('remuneration', 'Mission rémunérée ?'))
            ->add(DateTimeFilter::new('createdAtAnnouncement', 'Date de création'))
            ->add(EntityFilter::new('subCategoryAnnouncement', 'Métier associé'))
            ->add(EntityFilter::new('user', 'Utilisateur'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('user', 'Nom complet de l\'utilisateur'),
            TextField::new('typeAnnouncement', 'Type'),
            AssociationField::new('subCategoryAnnouncement', 'Métier recherché / Proposé'),
            TextField::new('departmentAnnouncement', 'Département'),
            TextField::new('cityAnnouncement', 'Ville'),
            DateTimeField::new('availabilityAnnouncement', 'Début du tournage / de disponibilité')
                ->setFormat('dd/MM/yyyy')
                ->setTimezone('Europe/Paris'),

            DateTimeField::new('expiryAnnouncement', 'Fin du tournage / de disponibilité:')
                ->setFormat('dd/MM/yyyy')
                ->setTimezone('Europe/Paris')
                ->formatValue(function ($value) {
                    if (!$value instanceof \DateTimeInterface) {
                        return '<span style="color:gray;">—</span>';
                    }

                    $today = new \DateTimeImmutable('today');
                    $diff = $today->diff($value)->days;
                    $isPast = $value < $today;

                    $color = match (true) {
                        $isPast => 'gray',
                        $diff <= 2 => 'red',
                        $diff <= 15 => 'orange',
                        default => 'green',
                    };

                    return sprintf('<span style="color:%s;">%s</span>', $color, $value->format('d/m/Y'));
                }),
            TextEditorField::new('textAnnouncement', 'Description'),
            BooleanField::new('remuneration', 'Mission rémunérée / Bénévolat')
                ->renderAsSwitch(false)
                ->formatValue(fn($value) => $value ? 'Oui' : 'Non'),

            TextField::new('linkAnnouncement', 'Email de contact')
                ->formatValue(function ($value) {
                    if (!$value) return '—';
                    return sprintf('<a href="mailto:%s">%s</a>', htmlspecialchars($value), htmlspecialchars($value));
                }),

            DateTimeField::new('createdAtAnnouncement', 'Date de création')
                ->onlyOnIndex()
                ->setTimezone('Europe/Paris'),
        ];
    }

    #[Route('/admin/export-announcements', name: 'export_announcements_csv')]
    public function exportCsv(CsvExporterService $csvExporter, ManagerRegistry $doctrine): Response
    {
        $announcements = $doctrine->getRepository(Announcement::class)->findAll();

        $data = [];
        foreach ($announcements as $a) {
            $data[] = [
                'Utilisateur' => $a->getUser()?->getFullName() ?? '—',
                'Type d\'annonce' => $a->getTypeAnnouncement(),
                'Métier associé' => $a->getSubCategoryAnnouncement()?->getNameSubCategory() ?? '—',
                'Département' => $a->getDepartmentAnnouncement(),
                'Ville' => $a->getCityAnnouncement(),
                'Début' => $a->getAvailabilityAnnouncement()?->format('d/m/Y') ?? '—',
                'Fin' => $a->getExpiryAnnouncement()?->format('d/m/Y') ?? '—',
                'Rémunérée' => $a->isRemuneration() ? 'Oui' : 'Non',
                'textAnnouncement' => $a->getTextAnnouncement() ?? '—',
                'Date de création' => $a->getCreatedAtAnnouncement()?->format('d/m/Y H:i') ?? '—',
                'Email de contact' => $a->getLinkAnnouncement() ?? '—',
            ];
        }

        $headers = [
            'Utilisateur',
            'Type d\'annonce',
            'Métier associé',
            'Département',
            'Ville',
            'Début',
            'Fin',
            'Rémunérée',
            'Texte de l\'annonce',
            'Date de création',
            'Email de contact'
        ];

        return $csvExporter->export($data, $headers, 'export-des-annonces.csv');
    }
}
