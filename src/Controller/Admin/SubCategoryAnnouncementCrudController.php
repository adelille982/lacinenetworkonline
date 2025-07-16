<?php

namespace App\Controller\Admin;

use App\Entity\SubCategoryAnnouncement;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CsvExporterService;
use Doctrine\Persistence\ManagerRegistry;

class SubCategoryAnnouncementCrudController extends AbstractCrudController
{

    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return SubCategoryAnnouncement::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportCsv = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_subcategories_csv')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary')
            ->setIcon('fa fa-download');

        return $actions
            ->add(Crud::PAGE_INDEX, $exportCsv)
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
            ->setEntityLabelInPlural('Sous-catégories d\'annonces')
            ->setEntityLabelInSingular('Sous-catégorie d\'annonce')
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(
                TextFilter::new('nameSubCategory', 'Nom de la sous-catégorie')
                    ->setFormTypeOption('attr', ['placeholder' => 'Chef opérateur·rice, Comédien·ne...'])
            )
            ->add(
                EntityFilter::new('categoryAnnouncement', 'Catégorie principale')
            );
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Informations générales')->setIcon('fa fa-tag'),
            TextField::new('nameSubCategory', 'Nom de la sous-catégorie')
                ->setHelp('
        <span style="color: red;">Nom affiché dans les filtres, les annonces et la navigation.</span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Bonnes pratiques de nommage (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Utilisez un nom <strong>court, explicite et sans abréviation</strong>.</li>
                    <li>Le nom doit représenter une fonction ou un domaine métier précis.</li>
                    <li>Exemples : <em>Chef opérateur</em>, <em>Comédien(ne)</em>, <em>Monteur son</em>.</li>
                </ul>
                <p><em>Un bon nom aide les utilisateurs à filtrer efficacement les annonces.</em></p>
            </div>
        </details>
    '),

            FormField::addTab('Catégorie associée')->setIcon('fa fa-folder-open'),
            AssociationField::new('categoryAnnouncement', 'Catégorie associée')
                ->formatValue(function ($value, $entity) {
                    if (!$value) {
                        return '<span style="color: gray;">—</span>';
                    }

                    $category = $entity->getCategoryAnnouncement();
                    $name = $category->getNameCategoryAnnouncement();
                    $color = $category->getColorCategoryAnnouncement();

                    $colorMap = [
                        'blue' => '#0057ff',
                        'green' => '#2ecc71',
                        'red' => '#e74c3c',
                        'yellow' => '#f1c40f',
                        'orange' => '#e67e22',
                        'purple' => '#9b59b6',
                        'cyan' => '#00bcd4',
                        'teal' => '#1abc9c',
                        'pink' => '#ff69b4',
                        'grey' => '#95a5a6',
                        'dark' => '#2c3e50',
                        'black' => '#000000',
                        'white' => '#ffffff',
                        'indigo' => '#3f51b5',
                        'lime' => '#cddc39',
                        'brown' => '#795548',
                        'deep-orange' => '#ff5722',
                        'amber' => '#ffc107',
                        'light-blue' => '#03a9f4',
                        'light-green' => '#8bc34a',
                        'deep-purple' => '#673ab7',
                        'blue-grey' => '#607d8b',
                    ];

                    $hex = $colorMap[$color] ?? '#999999';

                    return sprintf(
                        '<span style="display: inline-flex; align-items: center; gap: 8px;">
                <span style="display:inline-block;width:12px;height:12px;border-radius:50%%;background:%s;border:1px solid #ccc;"></span>
                <span>%s</span>
            </span>',
                        $hex,
                        htmlspecialchars($name)
                    );
                })
                ->renderAsHtml()
                ->setHelp('
        <span style="color: red;">Catégorie principale à laquelle cette sous-catégorie est rattachée.</span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Pourquoi ce lien est important ? (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li><strong>Organisation :</strong> chaque sous-catégorie doit appartenir à une catégorie principale (ex. : Artistes, Techniciens).</li>
                    <li><strong>Filtrage :</strong> permet à l’utilisateur de filtrer d’abord par catégorie, puis par sous-catégorie.</li>
                    <li><strong>Affichage :</strong> la couleur et le style dépendent de la catégorie liée.</li>
                </ul>
                <p><em>Assurez-vous de bien rattacher chaque sous-catégorie à une catégorie cohérente pour maintenir une structure claire.</em></p>
            </div>
        </details>
    '),
        ];
    }

    #[Route('/admin/export-subcategories', name: 'export_subcategories_csv')]
    public function exportCsv(CsvExporterService $csvExporter, ManagerRegistry $doctrine): Response
    {
        $subCategories = $doctrine->getRepository(SubCategoryAnnouncement::class)->findAll();

        $data = [];
        foreach ($subCategories as $sub) {
            $data[] = [
                'Nom de la sous-catégorie' => $sub->getNameSubCategory(),
                'Catégorie principale' => $sub->getCategoryAnnouncement()?->getNameCategoryAnnouncement() ?? '—',
                'Couleur' => $sub->getCategoryAnnouncement()?->getColorCategoryAnnouncement() ?? '—',
            ];
        }

        return $csvExporter->export(
            $data,
            ['Nom de la sous-catégorie', 'Catégorie principale', 'Couleur'],
            'export-sous-categories-annonces.csv'
        );
    }
}
