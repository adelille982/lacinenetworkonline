<?php

namespace App\Controller\Admin;

use App\Entity\CategoryAnnouncement;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CsvExporterService;
use Doctrine\Persistence\ManagerRegistry;

class CategoryAnnouncementCrudController extends AbstractCrudController
{
    private string $imageDir;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(ParameterBagInterface $params, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->imageDir = $params->get('kernel.project_dir') . '/public/images/annonce/image-catégorie-annonce/';
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return CategoryAnnouncement::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportCsv = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_category_announcement_csv')
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

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('nameCategoryAnnouncement', 'Nom de la catégorie'))
            ->add(EntityFilter::new('subCategoryAnnouncement', 'Sous-catégories associées'))
            ->add(
                ChoiceFilter::new('colorCategoryAnnouncement', 'Couleur')
                    ->setChoices([
                        'Bleu' => 'blue',
                        'Vert' => 'green',
                        'Rouge' => 'red',
                        'Jaune' => 'yellow',
                        'Orange' => 'orange',
                        'Violet' => 'purple',
                        'Cyan' => 'cyan',
                        'Turquoise (teal)' => 'teal',
                        'Rose' => 'pink',
                        'Gris' => 'grey',
                        'Sombre (dark)' => 'dark',
                        'Noir' => 'black',
                        'Blanc' => 'white',
                        'Indigo' => 'indigo',
                        'Citron vert (lime)' => 'lime',
                        'Brun' => 'brown',
                        'Orange foncé' => 'deep-orange',
                        'Ambre' => 'amber',
                        'Bleu clair' => 'light-blue',
                        'Vert clair' => 'light-green',
                        'Violet foncé' => 'deep-purple',
                        'Bleu-gris' => 'blue-grey',
                    ])
            );
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Catégories d\'annonces')
            ->setEntityLabelInSingular('Catégorie d\'annonce')
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $filesystem = new Filesystem();
        $choices = [];

        if ($filesystem->exists($this->imageDir)) {
            $images = array_diff(scandir($this->imageDir), ['.', '..']);
            $choices = array_combine($images, $images); // ex: ['image.jpg' => 'image.jpg']
        }

        return [
            FormField::addTab('Informations générales')->setIcon('fa fa-info-circle'),
            TextField::new('nameCategoryAnnouncement', 'Nom')
                ->setHelp('
        <span style="color: red;">Nom affiché publiquement pour cette catégorie d’annonces.</span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Bonnes pratiques pour nommer une catégorie (clique ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Choisissez un nom <strong>court, clair et significatif</strong> (ex. : <em>Technicien</em>, <em>Artiste</em>, <em>Régie</em>).</li>
                    <li>Utilisez une <strong>majuscule en début</strong> et évitez les caractères spéciaux ou les majuscules multiples.</li>
                    <li>Le nom doit pouvoir être compris seul, sans sous-titre.</li>
                </ul>
                <p><em>Ce nom structure les annonces et permet un filtrage rapide par les utilisateurs.</em></p>
            </div>
        </details>
    '),

            ChoiceField::new('colorCategoryAnnouncement', 'Couleur de la catégorie')
                ->setChoices([
                    'Bleu' => 'blue',
                    'Vert' => 'green',
                    'Rouge' => 'red',
                    'Jaune' => 'yellow',

                    'Orange' => 'orange',
                    'Violet' => 'purple',
                    'Cyan' => 'cyan',
                    'Turquoise (teal)' => 'teal',
                    'Rose' => 'pink',

                    'Gris' => 'grey',
                    'Sombre (dark)' => 'dark',
                    'Noir' => 'black',
                    'Blanc' => 'white',

                    'Indigo' => 'indigo',
                    'Citron vert (lime)' => 'lime',
                    'Brun' => 'brown',
                    'Orange foncé' => 'deep-orange',
                    'Ambre' => 'amber',
                    'Bleu clair' => 'light-blue',
                    'Vert clair' => 'light-green',
                    'Violet foncé' => 'deep-purple',
                    'Bleu-gris' => 'blue-grey',
                ])
                ->setHelp('
        <span style="color: red;">La couleur sera utilisée pour styliser visuellement les catégories sur le site.</span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Conseils pour choisir une couleur adaptée (clique ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Choisissez une couleur cohérente avec le thème de la catégorie.</li>
                    <li>Évitez les couleurs trop claires comme le blanc sauf si fond foncé.</li>
                    <li>Les couleurs sont utilisées dans les pastilles, textes ou badges visuels.</li>
                </ul>
                <p><em>Exemple : une catégorie “Technicien” peut être en bleu, “Artistique” en violet, “Son” en cyan.</em></p>
            </div>
        </details>
    ')
                ->setRequired(true)
                ->onlyOnForms(),

            TextField::new('colorCategoryAnnouncement', 'Couleur')
                ->hideOnForm()
                ->formatValue(function (?string $value) {
                    if (!$value) {
                        return '<span style="color: gray;">—</span>';
                    }

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

                    $hex = $colorMap[$value] ?? '#999999';

                    return sprintf(
                        '<span title="%s" style="display:inline-block;width:16px;height:16px;border-radius:50%%;background:%s;border:1px solid #ccc;"></span>',
                        htmlspecialchars($value),
                        $hex
                    );
                })
                ->renderAsHtml()
                ->setHelp('Pastille de couleur de la catégorie (affichage visuel dans le tableau d\'index).'),

            FormField::addTab('Image de la catégorie')->setIcon('fa fa-image'),

            ChoiceField::new('imgCategoryAnnouncement', 'Sélectionner une image pour la catégorie')
                ->setChoices($choices)
                ->setFormTypeOption('empty_data', '')
                ->setRequired(true)
                ->onlyOnForms()
                ->setHelp('
        <span style="color: red;">Image représentative de la catégorie dans les pages d’annonces.</span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Instructions pour bien choisir l’image (clique ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>L’image doit être préalablement ajoutée dans le dossier <code>/images/annonce/image-catégorie-annonce/</code>.</li>
                    <li>Format recommandé : <strong>JPG</strong>, <strong>WebP</strong> ou <strong>PNG</strong>.</li>
                    <li>Utilisez un visuel simple, sans texte, qui illustre clairement le métier ou le domaine.</li>
                    <li><em>Exemple : une caméra pour la catégorie “Image”, un micro pour “Son”, etc.</em></li>
                </ul>
                <p><em>Cette image aide l’utilisateur à identifier rapidement la catégorie dans l’interface.</em></p>
            </div>
        </details>
    '),

            ImageField::new('imgCategoryAnnouncement', 'Aperçu image')
                ->setBasePath('/images/annonce/image-categorie-annonce/')
                ->setUploadDir('public/images/annonce/image-categorie-annonce/')
                ->onlyOnIndex(),

            ImageField::new('imgCategoryAnnouncement', 'Image de la catégorie')
                ->setBasePath('/images/annonce/image-categorie-annonce/')
                ->onlyOnDetail(),

            FormField::addTab('Sous-catégories associées')->setIcon('fa fa-layer-group')
                ->hideOnForm(),
            AssociationField::new('subCategoryAnnouncement', 'Sous-catégories')
                ->hideOnForm()
                ->formatValue(function ($value) {
                    if ($value instanceof \Doctrine\Common\Collections\Collection && !$value->isEmpty()) {
                        return implode('<br>', $value->map(function ($sub) {
                            $url = $this->adminUrlGenerator
                                ->setController(\App\Controller\Admin\SubCategoryAnnouncementCrudController::class)
                                ->setAction('detail')
                                ->setEntityId($sub->getId())
                                ->generateUrl();

                            return sprintf('<a href="%s" style="display:inline-block; padding:4px 8px; border-radius:4px; margin:2px 0; text-decoration:none;">%s</a>', $url, htmlspecialchars($sub->getNameSubCategory()));
                        })->toArray());
                    }
                    return '<i>Aucune sous-catégorie associée</i>';
                })
                ->renderAsHtml()
                ->setHelp('
        <span style="color: red;">Liste des sous-catégories liées à cette catégorie.</span>
        <br><hr>
        <small>
            Chaque lien ouvre la fiche détail de la sous-catégorie concernée dans EasyAdmin.<br>
            Ce lien est uniquement visible depuis la fiche détaillée d’une catégorie.
        </small>
    ')
        ];
    }

    #[Route('/admin/export-category-announcement', name: 'export_category_announcement_csv')]
    public function exportCsv(CsvExporterService $csvExporter, ManagerRegistry $doctrine): Response
    {
        $categories = $doctrine->getRepository(CategoryAnnouncement::class)->findAll();

        $data = [];
        foreach ($categories as $cat) {
            $subCats = $cat->getSubCategoryAnnouncement()->map(fn($s) => $s->getNameSubCategory())->toArray();
            $data[] = [
                'Nom' => $cat->getNameCategoryAnnouncement(),
                'Couleur' => $cat->getColorCategoryAnnouncement(),
                'Sous-catégories' => implode(', ', $subCats)
            ];
        }

        return $csvExporter->export(
            $data,
            ['Nom', 'Couleur', 'Sous-catégories'],
            'export-categories-annonces.csv'
        );
    }
}
