<?php

namespace App\Controller\Admin;

use App\Entity\Partner;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CsvExporterService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;

class PartnerCrudController extends AbstractCrudController
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    public static function getEntityFqcn(): string
    {
        return Partner::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportCsvAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_partners_csv')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary')
            ->setIcon('fa fa-download');

        return $actions
            ->add(Crud::PAGE_INDEX, $exportCsvAction)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setLabel('Fiche')
                    ->setIcon('fa fa-user')
                    ->addCssClass('btn btn-info');
            });
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Partenaire')
            ->setEntityLabelInPlural('Partenaires')
            ->setDefaultSort(['id' => 'ASC'])
            ->setPaginatorPageSize(15);
    }

    public function configureFields(string $pageName): iterable
    {
        $imageDirectory = $this->projectDir . '/public/images/general/partenaires/';
        $filesystem = new Filesystem();
        $images = [];

        if ($filesystem->exists($imageDirectory)) {
            $images = array_diff(scandir($imageDirectory), ['.', '..']);
        }

        return [
            IdField::new('id', 'N°')->onlyOnIndex(),
            FormField::addTab('Nom du Partenaire')->setIcon('fas fa-user-tie'),

            TextField::new('namePartner', 'Nom du partenaire')
                ->setFormType(TextType::class)
                ->setFormTypeOptions(['attr' => ['placeholder' => 'Nom du partenaire']])
                ->setHelp(
                    '<span style="color: red;">
            Ce nom sera utilisé comme attribut <code>alt</code> de l’image pour les lecteurs d’écran. 
            <br>⚠️ Utilisez une formule explicite comme : <strong>Logo "Nom de l\'entreprise"</strong>.
        </span>
        <br>
        <small>
            Cela améliore l’accessibilité du site pour les utilisateurs malvoyants et favorise l’indexation des images par Google (SEO).
        </small>'
                ),

            FormField::addTab('Logo du Partenaire')->setIcon('fas fa-image'),
            TextField::new('logoPartner', 'Logo du partenaire')
                ->setFormType(TextType::class)
                ->setFormTypeOptions(['attr' => ['class' => 'filemanager']])
                ->onlyOnForms(),

            ChoiceField::new('logoPartner', 'Sélectionner un logo')
                ->setChoices(array_combine($images, $images))
                ->onlyOnForms(),

            ImageField::new('logoPartner', 'Aperçu du logo')
                ->setBasePath('/images/general/partenaires')
                ->setUploadDir('public/images/general/partenaires')
                ->onlyOnIndex(),

            ImageField::new('logoPartner', 'Logo du partenaire')
                ->setBasePath('/images/general/partenaires')
                ->onlyOnDetail(),

            FormField::addTab('Lien du partenaire')->setIcon('fas fa-link'),
            TextEditorField::new('linkPartner', 'Lien du partenaire')
                ->onlyOnForms()
                ->setHelp(
                    '<span style="color: red;">
            Veuillez toujours entrer un lien complet commençant par <strong>https://</strong> ou <strong>http://</strong>.
        </span>
        <br>
        <small>
            Exemple : <code>https://www.nom-du-partenaire.com</code><br>
            Cela garantit que le lien est valide et fonctionne correctement sur le site.
        </small>'
                ),

            TextField::new('linkPartner', 'Visiter le site')
                ->hideOnForm()
                ->formatValue(function ($value, $entity) {
                    if ($value) {
                        return sprintf('<a href="%s" target="_blank" class="btn btn-sm btn-primary">Visiter le lien du partenaire</a>', $value);
                    }
                    return '<span class="text-muted">Aucun lien</span>';
                })
                ->renderAsHtml(),
        ];
    }

    #[Route('/export-partners-csv', name: 'export_partners_csv')]
    public function exportCsv(CsvExporterService $csvExporter, ManagerRegistry $doctrine): Response
    {
        $partners = $doctrine->getRepository(Partner::class)->findAll();

        $data = [];
        foreach ($partners as $partner) {
            $data[] = [
                $partner->getNamePartner(),
                $partner->getLogoPartner(),
                $partner->getLinkPartner(),
            ];
        }

        $headers = ['Nom du partenaire', 'Fichier logo', 'Lien web'];

        return $csvExporter->export($data, $headers, 'liste-des-partenaires.csv');
    }
}
