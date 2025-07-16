<?php

namespace App\Controller\Admin;

use App\Entity\ArchivedEvent;
use App\Entity\Event;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use App\Service\CsvExporterService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;

class CurrentEventCrudController extends AbstractCrudController
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
        return Event::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportCsvAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_current_events_csv')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary');

        $exportParticipantsAction = Action::new('exportParticipants', 'Exporter participants', 'fa fa-users')
            ->linkToUrl(function ($entity) {
                return $this->adminUrlGenerator
                    ->setRoute('admin_event_export_participants', ['id' => $entity->getId()])
                    ->generateUrl();
            })
            ->addCssClass('btn btn-warning');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $exportCsvAction)
            ->add(Crud::PAGE_INDEX, $exportParticipantsAction)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setLabel('Fiche')
                    ->setIcon('fa fa-calendar-check')
                    ->addCssClass('btn btn-info');
            });
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('titleEvent')->setLabel('Titre de l’événement'))

            ->add(
                ChoiceFilter::new('typeEvent')->setLabel('Type d’événement')->setChoices([
                    'Network' => 'Network',
                    'Net Pitch' => 'Net Pitch',
                    'Location Network' => 'Location Network',
                    'Location Net Pitch' => 'Location Net Pitch',
                ])
            )

            ->add(DateTimeFilter::new('dateEvent')->setLabel('Date de l’événement'))
            ->add(TextFilter::new('numberEdition')->setLabel('Numéro d’édition'))
            ->add(EntityFilter::new('location')->setLabel('Lieu'))
            ->add(BooleanFilter::new('free')->setLabel('Événement gratuit'))
            ->add(TextFilter::new('priceEvent')->setLabel('Prix'))
            ->add(BooleanFilter::new('shortFilmProposal')->setLabel('Propositions de courts-métrages activées'))
            ->add(BooleanFilter::new('draft')->setLabel('Événement en brouillon'));
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Événement à venir')
            ->setEntityLabelInPlural('Événements à venir')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, $fields, $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $qb
            ->andWhere('entity.dateEvent >= :today')
            ->andWhere('entity.typeEvent IN (:types)')
            ->setParameter('today', new \DateTime())
            ->setParameter('types', ['Network', 'Net Pitch', 'Location Network', 'Location Net Pitch'])
            ->orderBy('entity.dateEvent', 'DESC');
    }

    public function configureFields(string $pageName): iterable
    {

        $imageDirectory = $this->projectDir . '/public/images/evenements/images-evenements/';
        $filesystem = new Filesystem();
        $images = [];

        if ($filesystem->exists($imageDirectory)) {
            $images = array_diff(scandir($imageDirectory), ['.', '..']);
        }

        return [
            FormField::addTab('Type de l\'événement')->setIcon('fas fa-tags'),
            ChoiceField::new('typeEvent', 'Type d\'événement')
                ->setChoices([
                    'Network' => 'Network',
                    'Net Pitch' => 'Net Pitch',
                    'Location Network' => 'Location Network',
                    'Location Net Pitch' => 'Location Net Pitch',
                ])
                ->renderExpanded(true)
                ->setRequired(true)
                ->setHelp('Sélectionner le type d\'événement'),

            FormField::addTab('Informations générales')->setIcon('fas fa-info-circle'),
            TextField::new('numberEdition', 'Numéro de l\'édition')
                ->setHelp('Numéro de l\'édition de l\'événement'),
            TextField::new('titleEvent', 'Titre de l\'événement')
                ->setHelp('Titre de l\'événement'),
            BooleanField::new('free', 'Événement gratuit')
                ->hideOnIndex(),
            TextField::new('priceEvent', 'Prix de l\'événement')
                ->hideOnIndex()
                ->setHelp('Laisser vide si l\'événement est gratuit')
                ->setRequired(false),
            DateTimeField::new('dateEvent', 'Date de l\'événement')
                ->setHelp('Date de l\'événement<br>
                Vous pouvez choisir une date antérieur si vous souhaitez créer un événement archivé.'),

            FormField::addTab('Image de l\'événement')->setIcon('fas fa-image'),
            TextField::new('imgEvent', 'Image de l\'événement')
                ->setFormType(TextType::class)
                ->setFormTypeOptions([
                    'attr' => ['class' => 'filemanager'],
                ])
                ->onlyOnForms(),

            ChoiceField::new('imgEvent', 'sélectionner une image de fond pour l\'événement')
                ->setChoices(array_combine($images, $images))
                ->setRequired(true)
                ->setFormTypeOption('empty_data', '')
                ->onlyOnForms()
                ->setHelp('<small>
        Sélectionnez une image déjà présente dans le dossier <code>/images-evenements/</code>.<br>
        Cette image apparaîtra sur les fiches événements. <br>
        <strong>Format conseillé :</strong> JPG ou WebP, format paysage.
    </small>'),

            ImageField::new('imgEvent', 'Image de fond de l\'événement')
                ->setBasePath('/images/evenements/images-evenements/')
                ->setUploadDir('public/images/evenements/images-evenements/')
                ->onlyOnIndex(),

            ImageField::new('imgEvent', 'Image de fond de l\'événement')
                ->setBasePath('/images/evenements/images-evenements/')
                ->setUploadDir('public/images/evenements/images-evenements/')
                ->onlyOnDetail(),

            FormField::addTab('Contenu')->setIcon('fas fa-align-left'),
            TextEditorField::new('textEvent', 'Description')
                ->hideOnIndex()
                ->formatValue(fn($value) => strip_tags($value))
                ->setHelp(
                    '<span style="color: red;">
        Ce champ permet de présenter l’événement de manière complète et engageante. Il sera visible publiquement.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Conseils pour rédiger la description de l’événement (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Rédigez d’abord votre contenu sur <strong>Word ou Google Docs</strong> (titres H1/H2, paragraphes), puis copiez-collez ici.</li>
                    <li>Introduisez le contexte, l’édition, les intervenants ou invités spéciaux, les thématiques abordées.</li>
                    <li>Utilisez un <strong>ton narratif et dynamique</strong>, adapté à un public passionné par le cinéma ou la culture.</li>
                    <li>Divisez le contenu en <strong>paragraphes courts</strong>, utilisez des listes si nécessaire.</li>
                    <li>Soignez l’orthographe, la ponctuation, et évitez les répétitions.</li>
                </ul>
                <p><em>Ce texte est souvent lu en premier par les visiteurs. Il doit donner envie de participer à l’événement.</em></p>
            </div>
        </details>'
                ),

            TextEditorField::new('programEvent', 'Programme')
                ->hideOnIndex()
                ->formatValue(fn($value) => strip_tags($value))
                ->setHelp(
                    '<span style="color: red;">
        Ce champ permet de détailler le déroulé de l’événement, heure par heure ou par grandes étapes.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Conseils pour structurer le programme (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Préparez le texte dans <strong>Word ou Google Docs</strong> (titres H1/H2, paragraphes) pour une meilleure mise en forme, puis copiez-collez ici.</li>
                    <li>Indiquez les <strong>horaires précis</strong> de chaque moment clé de l’événement.</li>
                    <li>Précisez les types d’activités : accueil, projection, débat, networking, remise de prix, etc.</li>
                    <li>Utilisez des listes à puces ou des blocs horaires (ex. : <code>18h30-20h00 : Networking</code>).</li>
                    <li>Facilitez la lecture avec une structure cohérente et aérée.</li>
                </ul>
                <p><em>Un programme bien rédigé aide les participants à se projeter dans l’événement et à s’organiser.</em></p>
            </div>
        </details>'
                ),

            FormField::addTab('Localisation de l\'événement')->setIcon('fas fa-map-marker-alt'),
            AssociationField::new('location', 'Lieu')
                ->setFormTypeOptions([
                    'query_builder' => function (\App\Repository\LocationRepository $repo) {
                        return $repo->createQueryBuilder('l')
                            ->where('l.typeLocation = :type')
                            ->setParameter('type', 'Événement');
                    },
                ])
                ->setHelp('Choississez parmis les lieux déjà créés.<br>
                Si le lieu n\'existe pas, vous pouvez le créer dans la section "Lieux".'),

            FormField::addTab('Courts métrages, Jurys & Participants')->setIcon('fas fa-users'),

            AssociationField::new('shortFilms', 'Courts-métrages diffusés')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'query_builder' => function (\App\Repository\ShortFilmRepository $repo) {
                        return $repo->createQueryBuilder('sf')
                            ->andWhere('sf.statutShortFilm IN (:validStatuses)')
                            ->setParameter('validStatuses', ['Produit', 'À financer']);
                    },
                ])
                ->setSortable(false)
                ->formatValue(function ($value, $entity) {
                    if ($value instanceof \Doctrine\Common\Collections\Collection) {
                        return implode('<br>', $value->map(function ($film) {
                            $url = $this->adminUrlGenerator
                                ->setController(\App\Controller\Admin\TotalShortFilmCrudController::class)
                                ->setAction('detail')
                                ->setEntityId($film->getId())
                                ->generateUrl();

                            return sprintf('<a href="%s">%s</a>', $url, $film->getTitleShortFilm());
                        })->toArray());
                    }
                    return '';
                })
                ->renderAsHtml()
                ->setHelp('Ajouter des courts-métrages de type "Produit" (pour les événements de type Network ou Location Network) ou "À financer" (pour les événements de type Net Pitch ou Location Net Pitch). Les intervenants de chaque film doivent être au statut validés.'),

            AssociationField::new('speakers', 'Jurys')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'query_builder' => function (\App\Repository\SpeakerRepository $repo) {
                        return $repo->createQueryBuilder('s')
                            ->andWhere('s.typeSpeaker = :type')
                            ->setParameter('type', 'Jury');
                    },
                ])
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
                                default => null,
                            };

                            if (!$controller) return (string)$speaker;

                            $url = $this->adminUrlGenerator
                                ->setController($controller)
                                ->setAction('detail')
                                ->setEntityId($speaker->getId())
                                ->generateUrl();

                            return sprintf('<a href="%s">%s</a>', $url, (string)$speaker);
                        })->toArray());
                    }
                    return '';
                })
                ->renderAsHtml()
                ->setHelp('Ajouter uniquement des intervenants de type "Jury" ayant le statut "Validé". Les événements de type "Network / Location Network" ne peuvent pas avoir de jury.'),

            Field::new('participantsList', 'Participants')
                ->hideOnForm()
                ->setVirtual(true)
                ->setTemplatePath('admin/current-event_participants.html.twig'),

            FormField::addTab('Propositions de courts métrages')->setIcon('fas fa-cogs'),
            BooleanField::new('shortFilmProposal', 'Activer les propositions de courts-métrages.')
                ->hideOnIndex()
                ->setHelp('<small>Si activé, l’intervenant ne sera pas visible sur le site public. Utilisé pour les fiches en cours de rédaction ou à valider.</small>'),

            FormField::addTab('Brouillon')->setIcon('fas fa-pencil-alt'),
            BooleanField::new('draft', 'Brouillon')
                ->setHelp('<small>Si activé, les propositions de court métrage seront visible sur le site public.</small>'),
        ];
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if (!$entityInstance instanceof Event) {
            parent::persistEntity($em, $entityInstance);
            return;
        }

        foreach ($entityInstance->getShortFilms() as $film) {
            $entityInstance->removeShortFilm($film);
            $entityInstance->addShortFilm($film);
        }

        foreach ($entityInstance->getSpeakers() as $speaker) {
            $entityInstance->removeSpeaker($speaker);
            $entityInstance->addSpeaker($speaker);
        }

        $now = new \DateTime();
        if ($entityInstance->getDateEvent() < $now) {
            $archived = new ArchivedEvent();
            $archived->setEvent($entityInstance);
            $archived->setArchivedAt($now);
            $archived->setDraft($entityInstance->isDraft());

            $em->persist($archived);
        }

        parent::persistEntity($em, $entityInstance);
    }

    #[Route('/admin/export-current-events-csv', name: 'export_current_events_csv')]
    public function exportCsv(CsvExporterService $csvExporter, EntityManagerInterface $em): Response
    {
        $today = new \DateTime();

        $events = $em->createQueryBuilder()
            ->select('e')
            ->from(Event::class, 'e')
            ->where('e.dateEvent >= :today')
            ->andWhere('e.typeEvent IN (:types)')
            ->setParameter('today', $today)
            ->setParameter('types', ['Network', 'Net Pitch', 'Location Network', 'Location Net Pitch'])
            ->orderBy('e.dateEvent', 'DESC')
            ->getQuery()
            ->getResult();

        $data = [];

        foreach ($events as $event) {
            $speakers = $event->getSpeakers()?->map(fn($s) => (string) $s)->toArray() ?? [];
            $speakerString = count($speakers) > 0 ? implode("\n", $speakers) : 'Aucun';

            $shortFilms = $event->getShortFilms()?->map(fn($f) => $f->getTitleShortFilm())->toArray() ?? [];
            $shortFilmsString = count($shortFilms) > 0 ? implode("\n", $shortFilms) : 'Aucun';

            $participants = $event->getUserEvents() ?? [];
            $participantsInfo = [];
            foreach ($participants as $userEvent) {
                $user = $userEvent->getUser();
                $participantsInfo[] = sprintf(
                    "%s %s\n%s\n%s",
                    $user->getFirstnameUser(),
                    $user->getLastnameUser(),
                    $user->getEmail(),
                    $user->getTelephoneUser()
                );
            }
            $participantsString = count($participantsInfo) > 0 ? implode("\n\n", $participantsInfo) : 'Aucun';

            $data[] = [
                $event->getTitleEvent() ?? 'N/A',
                $event->getNumberEdition() ?? 'N/A',
                $event->getTypeEvent(),
                $event->getDateEvent()?->format('d/m/Y') ?? 'N/A',
                $event->getLocation()?->__toString() ?? 'N/A',
                $event->isFree() ? 'Gratuit' : ($event->getPriceEvent() ?: 'Non précisé'),
                $shortFilmsString,
                $speakerString,
                $participantsString,
                $event->isShortFilmProposal() ? 'Oui' : 'Non',
                $event->isDraft() ? 'Brouillon' : 'Publiée',
            ];
        }

        $headers = [
            'Titre',
            'Édition',
            'Type',
            'Date',
            'Lieu',
            'Tarif',
            'Courts-métrages',
            'Jurys',
            'Participants',
            'Proposition de courts-métrages',
            'État de publication',
        ];

        return $csvExporter->export($data, $headers, 'evenements_a_venir.csv');
    }

    #[Route('/admin/event/{id}/export-participants', name: 'admin_event_export_participants')]
    public function exportParticipantsCsv(int $id, EntityManagerInterface $em, CsvExporterService $csvExporter): Response
    {
        /** @var Event|null $event */
        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }

        $participants = $event->getUserEvents();
        $data = [];

        foreach ($participants as $userEvent) {
            $user = $userEvent->getUser();
            $data[] = [
                $user->getFirstnameUser(),
                $user->getLastnameUser(),
                $user->getEmail(),
                $user->getTelephoneUser(),
            ];
        }

        $headers = ['Prénom', 'Nom', 'Email', 'Téléphone'];
        $title = $event->getTitleEvent();
        $normalizedTitle = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $title);
        $sanitizedTitle = preg_replace('/[^a-z0-9]+/i', '_', strtolower($normalizedTitle));
        $filename = sprintf('participants_%s_%s.csv', $event->getDateEvent()?->format('d-m-Y'), $sanitizedTitle);
        return $csvExporter->export($data, $headers, $filename);
    }
}
