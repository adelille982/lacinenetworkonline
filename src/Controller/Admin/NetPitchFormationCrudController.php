<?php

namespace App\Controller\Admin;

use App\Entity\NetPitchFormation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CsvExporterService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Filesystem\Filesystem;

class NetPitchFormationCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;
    private string $projectDir;

    public function __construct(AdminUrlGenerator $adminUrlGenerator, ParameterBagInterface $params)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->projectDir = $params->get('kernel.project_dir');
    }

    public static function getEntityFqcn(): string
    {
        return NetPitchFormation::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportCsvAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_netpitch_formations')
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
            ->setEntityLabelInSingular('Formation')
            ->setEntityLabelInPlural('Formations')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        $relativePdfPath = 'images/formation/pdf-des-formations/';
        $absolutePdfPath = $this->projectDir . '/public/' . $relativePdfPath;

        $filesystem = new Filesystem();
        $pdfs = [];

        if ($filesystem->exists($absolutePdfPath)) {
            $pdfs = array_values(array_filter(scandir($absolutePdfPath), function ($file) use ($absolutePdfPath) {
                return is_file($absolutePdfPath . '/' . $file) && preg_match('/\.pdf$/i', $file);
            }));
        }

        $pdfChoices = array_combine($pdfs, $pdfs);

        return [
            FormField::addTab('Informations générales')->setIcon('fa fa-info-circle'),
            IdField::new('id')
                ->hideOnIndex()
                ->hideOnForm()
                ->hideOnDetail(),
            TextField::new('slugNetPitchFormation', 'Slug (URL personnalisée)')
                ->hideOnIndex()
                ->setHelp('
                <span style="color: red;">
                    Le slug permet de personnaliser l’URL publique de la formation (ex. : <code>/lacinenetworknom-de-la-formation</code>).
                </span>
                <br><hr>
                <details>
                    <summary style="color: #FFA500; cursor: pointer;">Conseils pour bien choisir un slug</summary>
                    <div style="margin-top: 10px;">
                        <ul>
                            <li>Utilisez uniquement des lettres minuscules, des chiffres et des tirets (-).</li>
                            <li>Pas d’accents, d’espaces ni de caractères spéciaux.</li>
                            <li>Le slug doit être court, clair et représentatif du titre de la formation.</li>
                        </ul>
                        <p><em>Exemples : <code>ecriture-court-metrage</code>, <code>production-independante</code></em></p>
                    </div>
                </details>
            '),
            TextField::new('titleNetPitchFormation', 'Titre')
                ->setHelp('Titre complet de la formation (visible sur le site public).'),
            IntegerField::new('maxNumberNetPitchFormation', 'Nombre max. de participants')
                ->onlyOnForms()
                ->setHelp('Nombre total de participants autorisés pour cette formation.')
                ->setFormTypeOption('attr', ['min' => 1, 'step' => 1]),

            TextField::new('durationNetPitchFormation', 'Durée')->onlyOnForms()
                ->setHelp('Exemple : "120H - 10 JOURS - 2 SEMAINES"'),
            TextField::new('fundingNetPitchFormation', 'Financement')->onlyOnForms()
                ->setHelp('Indiquez les possibilités de financement (CPF, AFDAS, QUALIOPI etc.).'),

            FormField::addTab('Descriptions')->setIcon('fa fa-align-left'),

            TextEditorField::new('shortDescriptionNetPitchFormation', 'Description courte')
                ->formatValue(fn($value) => strip_tags($value))
                ->setHelp(
                    '<span style="color: red;">
        Cette description courte s’affiche sur les aperçus de formation. Elle doit captiver l’attention en quelques lignes.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Conseils pour une bonne description courte (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Rédigez d’abord sur Word ou Google Docs (titres H1/H2, paragraphes), puis copiez-collez ici.</li>
                    <li>Utilisez des phrases simples et percutantes.</li>
                    <li>Mettez en avant un bénéfice clé ou une accroche engageante.</li>
                    <li>Évitez les redondances avec le titre ou le slogan.</li>
                </ul>
                <p><em>Ce texte doit susciter l’envie de cliquer pour en savoir plus.</em></p>
            </div>
        </details>'
                ),

            TextEditorField::new('longDescriptionNetPitchFormation', 'Description longue')
                ->setHelp(
                    '<span style="color: red;">
        Cette description sera visible sur la page complète de la formation.
        Détaillez ici tout ce que le participant doit savoir.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Recommandations pour cette description (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Rédigez d’abord sur Word ou Google Docs (titres H1/H2, paragraphes), puis copiez-collez ici.</li>
                    <li>Structurez le contenu avec des titres (H2, H3) et des paragraphes clairs.</li>
                    <li>Incluez les objectifs, les compétences visées, les modalités pédagogiques, etc.</li>
                    <li>Soignez l’orthographe et évitez les blocs de texte trop denses.</li>
                    <li>Un ton professionnel mais accessible est recommandé.</li>
                </ul>
                <p><em>Cette partie constitue le cœur de la fiche formation. Soignez-la.</em></p>
            </div>
        </details>'
                ),

            TextEditorField::new('programDescription', 'Description programme')
                ->setRequired(true)
                ->formatValue(fn($value) => strip_tags($value))
                ->setHelp(
                    '<span style="color: red;">
        Utilisez ce champ pour présenter le programme détaillé ou les modules de la formation.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Suggestions pour structurer le programme (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Rédigez d’abord sur Word ou Google Docs (titres H1/H2, paragraphes), puis copiez-collez ici.</li>
                    <li>Divisez la formation par journées ou thématiques.</li>
                    <li>Indiquez clairement les contenus et activités pour chaque étape.</li>
                    <li>Facilitez la lecture avec des listes à puces ou tableaux si besoin.</li>
                    <li>Nommez les parties du programme (ex. : "Module 1 : Introduction", "Jour 2 : Pratique").</li>
                </ul>
                <p><em>Ce programme permet au participant de se projeter. Il doit être précis mais accessible.</em></p>
            </div>
        </details>'
                ),

            FormField::addTab('Gain associé')->setIcon('fa fa-link'),
            AssociationField::new('gain', 'Gain associé')
                ->setHelp('Sélectionnez un gain marketing ou une récompense liée à cette formation.'),

            FormField::addTab('Session associé')->setIcon('fa fa-link')
                ->hideOnForm(),
            AssociationField::new('sessionNetPitchFormations', 'Sessions associées')
                ->hideOnForm()
                ->setSortable(false)
                ->formatValue(function ($value, $entity) {
                    if ($value instanceof \Doctrine\Common\Collections\Collection) {
                        return implode('<br>', $value->map(function ($session) {
                            $controller = \App\Controller\Admin\SessionNetPitchFormationCrudController::class;

                            $url = $this->adminUrlGenerator
                                ->setController($controller)
                                ->setAction('detail')
                                ->setEntityId($session->getId())
                                ->generateUrl();

                            return sprintf('<a href="%s">%s</a>', $url, (string) $session);
                        })->toArray());
                    }
                    return '';
                })
                ->renderAsHtml()
                ->setHelp('Affiche toutes les sessions associées à cette formation. Cliquez pour accéder à la fiche session correspondante.'),

            FormField::addTab('Document PDF')->setIcon('fas fa-file-pdf'),

            ChoiceField::new('pdfNetPitchFormation', 'PDF de la formation')
                ->setChoices($pdfChoices)
                ->onlyOnForms()
                ->setHelp('
        <span style="color: red;">Le fichier PDF doit être déposé dans :</span><br>
        <code>/public/images/formation/pdf-des-formations/</code><br>
        Il sera proposé au téléchargement sur la fiche formation publique.
    '),

            TextField::new('pdfNetPitchFormation', 'PDF')
                ->onlyOnIndex()
                ->formatValue(
                    fn($value) =>
                    $value ? sprintf('<a href="/%s%s" target="_blank">📄 Voir le PDF</a>', $relativePdfPath, $value) : 'Aucun'
                )
                ->renderAsHtml(),


            FormField::addTab('SEO')->setIcon('fa fa-search'),
            TextareaField::new('metaDescriptionNetPitchFormation', 'Méta Description')->hideOnIndex()
                ->setHelp('
        <span style="color: red;">
            Ce champ est destiné aux moteurs de recherche. Il résume le contenu de la formation.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Recommandations SEO pour la méta description</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Faites une phrase d’environ 150 caractères.</li>
                    <li>Incluez les mots-clés principaux de la catégorie.</li>
                    <li>Évitez le copier-coller du titre ou du contenu.</li>
                    <li>Éveillez la curiosité de l’utilisateur pour l’inciter à cliquer.</li>
                </ul>
                <p><em>La méta description peut apparaître dans les résultats Google. Elle influence le taux de clic.</em></p>
            </div>
        </details>
    '),
            TextField::new('seoKeyNetPitchFormation', 'Mots-clés SEO')->hideOnIndex()
                ->setHelp('
        <span style="color: red;">
            Liste de mots-clés séparés par des virgules, utilisés pour renforcer le SEO de cette formation.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Comment choisir les bons mots-clés</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Utilisez des expressions réellement tapées par les internautes.</li>
                    <li>Variez les formulations : singulier/pluriel, synonymes, etc.</li>
                    <li><em>Exemple : “réalisation film, production court métrage, tournage cinéma indépendant”</em></li>
                    <li>Ne surchargez pas le champ, 5 à 7 mots-clés pertinents suffisent.</li>
                </ul>
                <p><em>Ces mots-clés sont utilisés en background pour améliorer la visibilité Google.</em></p>
            </div>
        </details>
    '),

            FormField::addTab('Brouillon')->setIcon('fas fa-pencil-alt'),
            BooleanField::new('draft', 'Brouillon')
                ->setHelp('<small>Si activé, la formation ne sera pas visible sur le site public. Utilisé pour les fiches en cours de rédaction ou à valider.</small>'),
        ];
    }

    #[Route('/admin/export-netpitch-formations', name: 'export_netpitch_formations')]
    public function exportCsv(CsvExporterService $csvExporter, EntityManagerInterface $em): StreamedResponse
    {
        $formations = $em->getRepository(NetPitchFormation::class)->findAll();

        $data = [];

        foreach ($formations as $formation) {
            $gain = $formation->getGain()?->getTitleGain() ?? 'Aucun';

            $sessions = $formation->getSessionNetPitchFormations();
            $sessionTitles = $sessions->map(fn($s) => (string) $s)->toArray();
            $sessionString = count($sessionTitles) > 0 ? implode("\n", $sessionTitles) : 'Aucune';

            $data[] = [
                $formation->getTitleNetPitchFormation(),
                $formation->getMaxNumberNetPitchFormation(),
                $formation->getDurationNetPitchFormation(),
                $formation->getFundingNetPitchFormation(),
                $formation->isDraft() ? 'Oui' : 'Non',
                $gain,
                $sessionString,
            ];
        }

        $headers = [
            'Titre',
            'Participants Max',
            'Durée',
            'Financement',
            'Brouillon',
            'Gain associé',
            'Sessions associées',
        ];

        return $csvExporter->export($data, $headers, 'formations.csv');
    }
}
