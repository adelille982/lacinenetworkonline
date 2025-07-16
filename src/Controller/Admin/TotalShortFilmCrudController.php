<?php

namespace App\Controller\Admin;

use App\Entity\ShortFilm;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CsvExporterService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class TotalShortFilmCrudController extends AbstractCrudController
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
        return ShortFilm::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportCsvAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_total_short_film_csv')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary')
            ->setIcon('fa fa-download');

        return $actions
            ->add(Crud::PAGE_INDEX, $exportCsvAction)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setLabel('Fiche')
                    ->setIcon('fas fa-video')
                    ->addCssClass('btn btn-info');
            });
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('titleShortFilm', 'Titre'))
            ->add(TextFilter::new('genreShortFilm', 'Genre'))
            ->add(TextFilter::new('durationShortFilm', 'Durée'))
            ->add(TextFilter::new('productionShortFilm', 'Production'))
            ->add(TextFilter::new('pitchShortFilm', 'Pitch'))

            ->add(
                ChoiceFilter::new('statutShortFilm', 'Statut')
                    ->setChoices([
                        'Produit' => 'Produit',
                        'À financer' => 'À financer',
                    ])
            )

            ->add(EntityFilter::new('speakers', 'Intervenants'))

            ->add(BooleanFilter::new('draft', 'Brouillon'));
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Court-métrage')
            ->setEntityLabelInPlural('Tous les courts-métrages')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $qb->andWhere('entity.statutShortFilm IN (:validStatuts)')
            ->setParameter('validStatuts', ['Produit', 'À financer']);

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        $filesystem = new Filesystem();

        $mainPosterDir = $this->projectDir . '/public/images/courts-metrages/';
        $mainPosters = [];
        if ($filesystem->exists($mainPosterDir)) {
            $mainPosters = array_diff(scandir($mainPosterDir), ['.', '..']);
        }

        $popupPosterDir = $this->projectDir . '/public/images/courts-metrages/courts-metrages-pop-up/';
        $popupPosters = [];
        if ($filesystem->exists($popupPosterDir)) {
            $popupPosters = array_diff(scandir($popupPosterDir), ['.', '..']);
        }

        return [
            FormField::addTab('Affiche principal')->setIcon('fas fa-image'),
            ChoiceField::new('posterShortFilm', 'Choisir une affiche principale')
                ->setChoices(array_combine($mainPosters, $mainPosters))
                ->onlyOnForms()
                ->setHelp('<small>
        Sélectionnez une image déjà présente dans le dossier <code>/courts-metrages</code>.<br>
        Cette image apparaîtra sur les fiches intervenants. <br>
        <strong>Format conseillé :</strong> JPG ou WebP, visage centré, format portrait.
    </small>'),
            ImageField::new('posterShortFilm', 'Affiche principale')
                ->setBasePath('/images/courts-metrages/')
                ->setUploadDir('/images/courts-metrages/')
                ->onlyOnIndex(),

            ImageField::new('posterShortFilm', 'Affiche principale')
                ->setBasePath('/images/courts-metrages/')
                ->setUploadDir('/images/courts-metrages/')
                ->onlyOnDetail(),

            FormField::addTab('Informations principales')->setIcon('fas fa-info-circle'),
            TextField::new('titleShortFilm', 'Titre du court-métrage')
                ->setHelp('Titre complet du court-métrage tel qu’il sera affiché sur le site.'),
            TextField::new('genreShortFilm', 'Genre')
                ->setHelp('Exemple : Drame, Comédie, Science-fiction, etc.')
                ->setRequired(true),
            TextField::new('durationShortFilm', 'Durée')
                ->setHelp('Durée du film en minutes, ex : 12 min.')
                ->setRequired(true),
            TextField::new('productionShortFilm', 'Production')
                ->setHelp('Nom de la société ou personne produisant le court-métrage.')
                ->setRequired(true),

            FormField::addTab('Intervenant associé au court métrage')->setIcon('fas fa-users'),
            AssociationField::new('speakers', 'Intervenant')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'query_builder' => function (\App\Repository\SpeakerRepository $repo) {
                        return $repo->createQueryBuilder('s')
                            ->where('s.typeSpeaker IN (:types)')
                            ->andWhere('s.statutSpeaker = :statut')
                            ->setParameter('types', ['Réalisateur', 'Stagiaire'])
                            ->setParameter('statut', 'Validé');
                    },
                ])
                ->setHelp('Seuls les intervenants "Réalisateur" ou "Stagiaire" validés peuvent être sélectionnés.')
                ->setRequired(true)
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
                            };

                            $url = $this->adminUrlGenerator
                                ->setController($controller)
                                ->setAction('detail')
                                ->setEntityId($speaker->getId())
                                ->generateUrl();

                            return sprintf('<a href="%s">%s</a>', $url, (string)$speaker);
                        })->toArray());
                    }
                    return '';
                })->renderAsHtml(),

            FormField::addTab('Pitch du court métrage')->setIcon('fas fa-pen-nib'),
            TextEditorField::new('pitchShortFilm', 'Pitch')
                ->setRequired(true)
                ->formatValue(fn($value) => strip_tags($value))
                ->setHelp(
                    '<span style="color: red;">
        Ce champ correspond au résumé narratif du court-métrage. Il sera affiché dans les fiches et pop-ups.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Conseils pour rédiger un bon pitch (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Rédigez d’abord votre texte dans <strong>Word ou Google Docs</strong> (titres H1/H2, paragraphes), puis copiez-collez ici.</li>
                    <li>Présentez le protagoniste, son objectif, les enjeux et le cadre de l’histoire.</li>
                    <li>Utilisez un <strong>style immersif</strong> et narratif, sans tout dévoiler.</li>
                    <li>Soyez synthétique mais engageant.</li>
                    <li>Évitez les spoilers, conservez l’effet de surprise.</li>
                </ul>
                <p><em>Le pitch donne envie de voir le film. Il doit intriguer, séduire et informer en un instant.</em></p>
            </div>
        </details>'
                ),

            FormField::addTab('Image de fond de la pop up')->setIcon('fas fa-images'),
            ChoiceField::new('posterPopUpShortFilm', 'Choisir une affiche de fond pour la pop-up')
                ->setChoices(array_combine($popupPosters, $popupPosters))
                ->onlyOnForms()
                ->setRequired(true)
                ->setHelp('<small>
        Image qui s’affichera dans la fenêtre pop-up de présentation rapide.<br>
        <strong>Conseil :</strong> Choisissez une image différente de l\'affiche de film pour varier la présentation.<br>
        Format paysage recommandé.
    </small>'),

            ImageField::new('posterPopUpShortFilm', 'Image pop-up')
                ->setBasePath('/images/courts-metrages/courts-metrages-pop-up/')
                ->setUploadDir('/images/courts-metrages/courts-metrages-pop-up/')
                ->onlyOnIndex(),

            ImageField::new('posterPopUpShortFilm', 'Image pop-up')
                ->setBasePath('/images/courts-metrages/courts-metrages-pop-up/')
                ->setUploadDir('/images/courts-metrages/courts-metrages-pop-up/')
                ->onlyOnDetail(),

            FormField::addTab('Statut du film')->setIcon('fas fa-film'),
            ChoiceField::new('statutShortFilm', 'Statut')
                ->setChoices([
                    'Produit' => 'Produit',
                    'À financer' => 'À financer',
                ])
                ->renderExpanded(true)
                ->setRequired(true)
                ->setHelp('Définissez si le court-métrage est déjà produit ou en recherche de financement.'),

            FormField::addTab('Brouillon')->setIcon('fas fa-pencil-alt'),
            BooleanField::new('draft', 'Brouillon')
                ->setHelp('Si activé, ce court-métrage ne sera pas affiché sur le site public.'),
        ];
    }

    #[Route('/export-total-short-film-csv', name: 'export_total_short_film_csv')]
    public function exportCsv(CsvExporterService $csvExporter, ManagerRegistry $doctrine): Response
    {
        $totalShortFilm = $doctrine->getRepository(ShortFilm::class)->findAll();

        $data = [];

        foreach ($totalShortFilm as $shortFilm) {
            $speakers = implode(', ', $shortFilm->getSpeakers()->map(
                fn($s) =>
                $s->getFirstNameSpeaker() . ' ' . $s->getLastNameSpeaker()
            )->toArray());

            $pitch = str_replace(["\n", "\r"], ' ', $shortFilm->getPitchShortFilm() ?? '');

            $data[] = [
                $shortFilm->getTitleShortFilm(),
                $shortFilm->getGenreShortFilm(),
                $shortFilm->getDurationShortFilm(),
                $shortFilm->getProductionShortFilm(),
                $speakers,
                $pitch,
                $shortFilm->getStatutShortFilm(),
                $shortFilm->isDraft() ? 'Oui' : 'Non',
            ];
        }

        $headers = [
            'Titre du court-métrage',
            'Genre',
            'Durée',
            'Production',
            'Intervenants',
            'Pitch',
            'Statut',
            'Brouillon'
        ];

        return $csvExporter->export($data, $headers, 'export-tous-les-courts-metrages.csv');
    }
}
