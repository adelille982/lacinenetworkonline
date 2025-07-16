<?php

namespace App\Controller\Admin;

use App\Entity\Speaker;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CsvExporterService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class JuryCrudController extends AbstractCrudController
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
            ->add(TextFilter::new('newsSpeaker', 'Actualité'))
            ->add(BooleanFilter::new('draft', 'Brouillon'));
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Intervenant Jury')
            ->setEntityLabelInPlural('Intervenants Jury')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportCsvAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_speakers_jury_csv')
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

        return $qb->andWhere('entity.typeSpeaker = :juryType')
            ->setParameter('juryType', 'Jury');
    }

    public function configureFields(string $pageName): iterable
    {
        $profileDir = $this->projectDir . '/public/images/intervenants/jury/jury-profil/';
        $popupDir = $this->projectDir . '/public/images/intervenants/jury/jury-pop-up/';
        $logoDir = $this->projectDir . '/public/images/intervenants/logos-entreprises/';

        $filesystem = new Filesystem();

        $profileImages = $filesystem->exists($profileDir) ? array_diff(scandir($profileDir), ['.', '..']) : [];
        $popupImages = $filesystem->exists($popupDir) ? array_diff(scandir($popupDir), ['.', '..']) : [];
        $logoImages = $filesystem->exists($logoDir) ? array_diff(scandir($logoDir), ['.', '..']) : [];

        return [
            FormField::addTab('Identité')->setIcon('fa fa-user'),
            ChoiceField::new('typeSpeaker', 'Type')
                ->setChoices([
                    'Jury' => 'Jury',
                ]),
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
                ->setHelp('<small>Indiquez le rôle exact de l’intervenant dans l’entreprise (ex : Présentateur Canal +, etc ...). <br>
                Il sera utilisé dans les présentations et fiches intervenant</small>'),
            ChoiceField::new('statutSpeaker', 'Statut de l’intervenant')->setChoices(['Validé' => 'Validé'])
                ->setHelp('<small>Statut de l’intervenant (ex : Validé, Proposé)</small>'),

            FormField::addTab('Biographie')->setIcon('fa fa-book'),
            TextEditorField::new('biographySpeaker', 'Biographie')
                ->formatValue(fn($value) => strip_tags($value))
                ->setHelp(
                    '<span style="color: red;">
        Présentez ici le parcours professionnel et artistique du membre du jury.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Conseils pour rédiger une biographie efficace (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Rédigez d’abord votre contenu sur <strong>Word ou Google Docs</strong> (titres, paragraphes), puis copiez-collez ici.</li>
                    <li>Évoquez les formations, expériences marquantes, distinctions, rôles dans le domaine cinématographique.</li>
                    <li>Utilisez un <strong>style clair, concis et professionnel</strong>.</li>
                    <li>Adaptez le ton selon le profil (cinéaste, critique, acteur·rice, etc.).</li>
                </ul>
                <p><em>La biographie sera lue par les visiteurs du site. Elle doit valoriser l’expertise et la légitimité du jury.</em></p>
            </div>
        </details>'
                ),

            TextEditorField::new('newsSpeaker', 'Actualité')
                ->formatValue(fn($value) => strip_tags($value))
                ->setHelp(
                    '<span style="color: red;">
        Ce champ met en lumière une actualité récente ou notable du membre du jury.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Exemples et suggestions d’actualités pertinentes (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Sortie d’un nouveau film, participation à un festival, nomination ou prix reçu.</li>
                    <li>Engagement dans une œuvre artistique ou sociale récente.</li>
                    <li>Collaboration avec une personnalité ou une institution connue.</li>
                    <li>Veillez à ce que l’actualité soit récente et en lien avec son rôle au sein du jury.</li>
                </ul>
                <p><em>Une actualité bien choisie renforce la visibilité et l’impact du membre du jury auprès du public.</em></p>
            </div>
        </details>'
                ),

            FormField::addTab('Images')->setIcon('fa fa-image'),

            ChoiceField::new('pictureSpeaker', 'Photo de profil')
                ->setChoices(array_combine($profileImages, $profileImages))
                ->onlyOnForms()
                ->setHelp('<small>
        Sélectionnez une image déjà présente dans le dossier <code>/jury-profil/</code>.<br>
        Cette image apparaîtra sur les fiches intervenants. <br>
        <strong>Format conseillé :</strong> JPG ou WebP, visage centré, format portrait.
    </small>'),
            ImageField::new('pictureSpeaker', 'Photo de profil')
                ->setBasePath('/images/intervenants/jury/jury-profil/')
                ->setUploadDir('public/images/intervenants/jury/jury-profil/')
                ->onlyOnIndex(),

            ImageField::new('pictureSpeaker', 'Photo de profil')
                ->setBasePath('/images/intervenants/jury/jury-profil/')
                ->setUploadDir('public/images/intervenants/jury/jury-profil/')
                ->onlyOnDetail(),

            ChoiceField::new('imgPopUpSpeaker', 'Image pop-up')
                ->setChoices(array_combine($popupImages, $popupImages))
                ->setRequired(true)
                ->onlyOnForms()
                ->setHelp('<small>
        Image qui s’affichera dans la fenêtre pop-up de présentation rapide.<br>
        <strong>Conseil :</strong> Choisissez une image différente de la photo de profil pour varier la présentation.<br>
        Format paysage recommandé.
    </small>'),
            ImageField::new('imgPopUpSpeaker', 'Image pop-up')
                ->setBasePath('/images/intervenants/jury/jury-pop-up/')
                ->setUploadDir('public/images/intervenants/jury/jury-pop-up/')
                ->onlyOnIndex(),

            ImageField::new('imgPopUpSpeaker', 'Image pop-up')
                ->setBasePath('/images/intervenants/jury/jury-pop-up/')
                ->setUploadDir('public/images/intervenants/jury/jury-pop-up/')
                ->onlyOnDetail(),

            ChoiceField::new('pictureCompanySpeaker', 'Logo entreprise')
                ->setChoices(array_combine($logoImages, $logoImages))
                ->onlyOnForms()
                ->setHelp('<small>
        Logo de la société de production ou structure représentée par l’intervenant.<br>
        Le fichier doit être déjà présent dans <code>/logos-entreprises</code>.<br>
        <strong>Format conseillé :</strong> carré ou horizontal, PNG ou WebP avec fond transparent.
    </small>'),
            ImageField::new('pictureCompanySpeaker', 'Logo entreprise')
                ->setBasePath('/images/intervenants/logos-entreprises')
                ->setUploadDir('public/images/intervenants/logos-entreprises/')
                ->onlyOnIndex(),

            ImageField::new('pictureCompanySpeaker', 'Logo entreprise')
                ->setBasePath('/images/intervenants/logos-entreprises')
                ->setUploadDir('public/images/intervenants/logos-entreprises/')
                ->onlyOnDetail(),

            FormField::addTab('Réseaux')->setIcon('fa fa-link'),
            TextField::new('instagramSpeaker', 'Instagram')->hideOnIndex()
                ->setHelp('<small>Collez le lien complet du profil, ex : <code>https://www.instagram.com/nom_utilisateur</code></small>'),
            TextField::new('facebookSpeaker', 'Facebook')->hideOnIndex()
                ->setHelp('<small>Collez le lien complet du profil ou de la page. Exemple : <code>https://www.facebook.com/nom_page</code></small>'),

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
                ->setHelp('<small>Si activé, l’intervenant ne sera pas visible sur le site public. Utilisé pour les fiches en cours de rédaction ou à valider.</small>'),
        ];
    }

    #[Route('/export-speakers-jury-csv', name: 'export_speakers_jury_csv')]
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
                $speaker->getNewsSpeaker(),
                $speaker->getInstagramSpeaker(),
                $speaker->getFacebookSpeaker(),
                $speaker->isDraft() ? 'Oui' : 'Non',
            ];
        }

        $headers = ['Prénom', 'Nom', 'Téléphone', 'Email', 'Statut', 'Type', 'Rôle', 'Biographie', 'Actualités', 'Instagram', 'Facebook', 'Brouillon'];

        return $csvExporter->export($data, $headers, 'export-de-tous-les-intervenants-de-type-jury.csv');
    }
}
