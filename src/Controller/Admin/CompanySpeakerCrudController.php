<?php

namespace App\Controller\Admin;

use App\Entity\Speaker;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CsvExporterService;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class CompanySpeakerCrudController extends AbstractCrudController
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    public static function getEntityFqcn(): string
    {
        return Speaker::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('lastNameSpeaker', 'Nom'))
            ->add(TextFilter::new('firstNameSpeaker', 'Prénom'))
            ->add(TextFilter::new('roleSpeaker', 'Rôle'))
            ->add(TextFilter::new('biographySpeaker', 'Biographie'))
            ->add(BooleanFilter::new('draft', 'Brouillon'));
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Intervenant Entreprise')
            ->setEntityLabelInPlural('Intervenants Entreprise')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportCsvAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_speakers_company_csv')
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

    public function createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $qb->andWhere('entity.typeSpeaker = :entrepriseType')
            ->setParameter('entrepriseType', 'Entreprise');
    }

    public function configureFields(string $pageName): iterable
    {
        $profileDir = $this->projectDir . '/public/images/intervenants/entreprise/entreprise-profil/';

        $filesystem = new Filesystem();

        $profileImages = $filesystem->exists($profileDir) ? array_diff(scandir($profileDir), ['.', '..']) : [];

        return [
            FormField::addTab('Identité')->setIcon('fa fa-user'),
            ChoiceField::new('typeSpeaker', 'Type')->setChoices(['Entreprise' => 'Entreprise']),
            TextField::new('lastNameSpeaker', 'Nom')
                ->setHelp('<small>Nom de famille de l’intervenant. Il sera utilisé dans les présentations et fiches intervenant.</small>'),
            TextField::new('firstNameSpeaker', 'Prénom')
                ->setHelp('<small>Prénom de l’intervenant. Il sera utilisé dans les présentations et fiches intervenant.</small>'),

            TextField::new('telSpeaker', 'Téléphone')
                ->setHelp('<small>Numéro de téléphone de l’intervenant.</small>')
                ->hideOnIndex(),

            TextField::new('emailSpeaker', 'Email')
                ->setHelp('<small>Adresse email de l’intervenant.</small>')
                ->hideOnIndex(),

            TextField::new('roleSpeaker', 'Rôle')
                ->setHelp('<small>Indiquez le rôle exact de l’intervenant dans l’entreprise (ex : Responsable RH, CEO...). <br>
                Il sera utilisé dans les présentations et fiches intervenant</small>'),
            ChoiceField::new('statutSpeaker', 'Statut de l’intervenant')->setChoices(['Validé' => 'Validé'])
                ->setHelp('<small>Statut de l’intervenant (ex : Validé, Proposé)</small>'),

            FormField::addTab('Biographie')->setIcon('fa fa-book'),
            TextEditorField::new('biographySpeaker', 'Biographie')
                ->formatValue(fn($value) => strip_tags($value))
                ->setHelp(
                    '<span style="color: red;">
        Présentez ici le parcours et les compétences principales de l’intervenant.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Conseils pour rédiger la biographie (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Rédigez sur <strong>Word ou Google Docs</strong> (titres, paragraphes), puis copiez-collez ici.</li>
                    <li>Indiquez les diplômes, formations, expériences significatives ou spécialisations.</li>
                    <li>Mettez en avant les expertise.</li>
                    <li>Utilisez des phrases courtes, actives et bien ponctuées.</li>
                    <li>Structurez le texte avec des sous-titres si besoin.</li>
                </ul>
                <p><em>Cette biographie est affichée publiquement sur la fiche intervenant. Elle doit inspirer confiance et refléter le sérieux de la personne.</em></p>
            </div>
        </details>'
                ),

            FormField::addTab('Images')->setIcon('fa fa-image'),

            // Photo de profil
            ChoiceField::new('pictureSpeaker', 'Photo de profil')
                ->setChoices(array_combine($profileImages, $profileImages))
                ->onlyOnForms()
                ->setHelp('<small>Sélectionnez une photo de profil (visage) déjà présente dans le répertoire <code>/entreprise-profil</code>.</small>'),
            ImageField::new('pictureSpeaker', 'Photo de profil')
                ->setBasePath('/images/intervenants/entreprise/entreprise-profil/')
                ->setUploadDir('public/images/intervenants/entreprise/entreprise-profil/')
                ->onlyOnIndex(),

            ImageField::new('pictureSpeaker', 'Photo de profil')
                ->setBasePath('/images/intervenants/entreprise/entreprise-profil/')
                ->setUploadDir('public/images/intervenants/entreprise/entreprise-profil/')
                ->onlyOnDetail(),

            FormField::addTab('Réseaux')->setIcon('fa fa-link'),
            TextField::new('instagramSpeaker', 'Instagram')->hideOnIndex()
                ->setHelp('<small>Insérez le lien complet, exemple : <code>https://www.instagram.com/nom_compte</code></small>'),
            TextField::new('facebookSpeaker', 'Facebook')->hideOnIndex()
                ->setHelp('<small>Insérez le lien complet, exemple : <code>https://www.facebook.com/nom_page</code></small>'),

            FormField::addTab('Villes associées')->setIcon('fa fa-city'),

            // Vue liste (index)
            TextareaField::new('locationsLinks', 'Villes associées')
                ->onlyOnIndex()
                ->formatValue(fn($value, $entity) => $entity->getLocationsLinks())
                ->renderAsHtml(),

            // Vue détail
            TextareaField::new('locationsLinks', 'Villes associées')
                ->onlyOnDetail()
                ->formatValue(fn($value, $entity) => $entity->getLocationsLinks())
                ->renderAsHtml(),

            // Formulaire
            AssociationField::new('locations', 'Villes d’intervention')
                ->setHelp('Sélectionnez les villes dans lesquelles le formateur intervient')
                ->setFormTypeOptions(['by_reference' => false])
                ->setRequired(false)
                ->onlyOnForms(),

            FormField::addTab('Brouillon')->setIcon('fas fa-pencil-alt'),
            BooleanField::new('draft', 'Brouillon')
                ->setHelp('<small>Si activé, l’intervenant n’apparaîtra pas sur le site public.</small>'),
        ];
    }

    #[Route('/export-speakers-company-csv', name: 'export_speakers_company_csv')]
    public function exportCsv(CsvExporterService $csvExporter, ManagerRegistry $doctrine): Response
    {
        $totalSpeakers = $doctrine->getRepository(Speaker::class)->findAll();

        $data = [];
        foreach ($totalSpeakers as $speaker) {
            $data[] = [
                $speaker->getFirstNameSpeaker(),
                $speaker->getLastNameSpeaker(),
                $speaker->getTelSpeaker(),
                $speaker->getEmailSpeaker(),
                $speaker->getStatutSpeaker(),
                $speaker->getTypeSpeaker(),
                $speaker->getRoleSpeaker(),
                $speaker->getBiographySpeaker(),
                $speaker->getInstagramSpeaker(),
                $speaker->getFacebookSpeaker(),
                $speaker->isDraft() ? 'Oui' : 'Non',
            ];
        }

        $headers = ['Prénom', 'Nom', 'Téléphone', 'Email', 'Statut', 'Type', 'Rôle', 'Biographie', 'Instagram', 'Facebook', 'Brouillon'];

        return $csvExporter->export($data, $headers, 'export-de-tous-les-intervenants-de-type-entreprise.csv');
    }
}
