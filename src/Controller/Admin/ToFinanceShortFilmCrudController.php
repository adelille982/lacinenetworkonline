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
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CsvExporterService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;


class ToFinanceShortFilmCrudController extends AbstractCrudController
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
            ->linkToRoute('export_total_short_film_to_finance_csv')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary')
            ->setIcon('fa fa-download');

        $markAsProduced = Action::new('markAsProduced', '🎬 Produit')
            ->linkToUrl(function (ShortFilm $film) {
                return $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction('markAsProduced')
                    ->setEntityId($film->getId())
                    ->generateUrl();
            })
            ->addCssClass('btn btn-sm btn-success');

        $markAsProposed = Action::new('markAsProposed', '🔙 Proposé')
            ->linkToUrl(function (ShortFilm $film) {
                return $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction('markAsProposed')
                    ->setEntityId($film->getId())
                    ->generateUrl();
            })
            ->addCssClass('btn btn-sm btn-secondary');

        return $actions
            ->add(Crud::PAGE_INDEX, $markAsProduced)
            ->add(Crud::PAGE_INDEX, $markAsProposed)
            ->add(Crud::PAGE_INDEX, $exportCsvAction)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setLabel('Fiche')
                    ->setIcon('fa fa-video')
                    ->addCssClass('btn btn-info');
            })
            ->disable(Action::NEW, Action::EDIT);
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
            ->setEntityLabelInSingular('Court-métrage à financer')
            ->setEntityLabelInPlural('Tous les courts-métrages à financer')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $qb->andWhere('entity.statutShortFilm IN (:validStatuts)')
            ->setParameter('validStatuts', ['À financer']);

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        $imageDirectory = $this->projectDir . '/public/images/courts-metrages/';
        $filesystem = new Filesystem();
        $images = [];

        if ($filesystem->exists($imageDirectory)) {
            $images = array_diff(scandir($imageDirectory), ['.', '..']);
        }

        return [
            FormField::addTab('Affiche principal')->setIcon('fas fa-image'),
            ChoiceField::new('posterShortFilm', 'Choisir une affiche principale')
                ->setChoices(array_combine($images, $images))
                ->onlyOnForms(),

            ImageField::new('posterShortFilm', 'Affiche principale')
                ->setBasePath('/images/courts-metrages/')
                ->setUploadDir('public/images/courts-metrages/')
                ->onlyOnIndex(),

            ImageField::new('posterShortFilm', 'Affiche principale')
                ->setBasePath('/images/courts-metrages/')
                ->setUploadDir('public/images/courts-metrages/')
                ->onlyOnDetail(),

            FormField::addTab('Informations principales')->setIcon('fas fa-info-circle'),
            TextField::new('titleShortFilm', 'Titre du court-métrage'),
            TextField::new('genreShortFilm', 'Genre'),
            TextField::new('durationShortFilm', 'Durée'),
            TextField::new('productionShortFilm', 'Production'),
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
                ->formatValue(function ($value) {
                    return strip_tags($value);
                }),

            FormField::addTab('Image de fond de la pop up')->setIcon('fas fa-film'),
            ChoiceField::new('posterPopUpShortFilm', 'Choisir une Image de fond pour la pop up')
                ->setChoices(array_combine($images, $images))
                ->onlyOnForms(),

            ImageField::new('posterPopUpShortFilm', 'Image pop up')
                ->setBasePath('/images/courts-metrages/courts-metrages-pop-up/')
                ->setUploadDir('public/images/courts-metrages/courts-metrages-pop-up/')
                ->onlyOnIndex(),

            ImageField::new('posterPopUpShortFilm', 'Image pop up')
                ->setBasePath('/images/courts-metrages/courts-metrages-pop-up/')
                ->setUploadDir('public/images/courts-metrages/courts-metrages-pop-up/')
                ->onlyOnDetail(),

            FormField::addTab('Statut du film')->setIcon('fas fa-film'),
            ChoiceField::new('statutShortFilm', 'Statut')
                ->setChoices([
                    'À financer' => 'À financer',
                ])
                ->renderExpanded(true)
                ->setRequired(true),

            FormField::addTab('brouillon')->setIcon('fas fa-pencil-alt'),
            BooleanField::new('draft', 'Brouillon'),
        ];
    }

    public function markAsProduced(AdminContext $context, ManagerRegistry $doctrine): RedirectResponse
    {
        $id = $context->getRequest()->query->get('entityId');
        $em = $doctrine->getManager();
        $shortFilm = $em->getRepository(ShortFilm::class)->find($id);

        if ($shortFilm) {
            foreach ($shortFilm->getSpeakers() as $speaker) {
                $speaker->setTypeSpeaker('Réalisateur');
                $speaker->setStatutSpeaker('Validé');
            }

            $shortFilm->setStatutShortFilm('Produit');
            $em->flush();

            $this->addFlash('success', 'Court-métrage validé : Produit. Intervenant promu Réalisateur validé.');
        }

        return $this->redirect($this->adminUrlGenerator->setController(self::class)->setAction('index')->generateUrl());
    }

    public function markAsProposed(AdminContext $context, ManagerRegistry $doctrine): RedirectResponse
    {
        $id = $context->getRequest()->query->get('entityId');
        $em = $doctrine->getManager();
        $shortFilm = $em->getRepository(ShortFilm::class)->find($id);

        if ($shortFilm) {
            foreach ($shortFilm->getSpeakers() as $speaker) {
                $speaker->setTypeSpeaker('Externe');
                $speaker->setStatutSpeaker('Proposé');
            }

            $shortFilm->setStatutShortFilm('Proposé');
            $em->flush();

            $this->addFlash('info', 'Court-métrage remis en "Proposé". Intervenant redevenu Externe proposé.');
        }

        return $this->redirect($this->adminUrlGenerator->setController(self::class)->setAction('index')->generateUrl());
    }

    #[Route('/export-total-short-film-to-finance-csv', name: 'export_total_short_film_to_finance_csv')]
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

        return $csvExporter->export($data, $headers, 'export-tous-les-courts-metrages-a-finance.csv');
    }
}
