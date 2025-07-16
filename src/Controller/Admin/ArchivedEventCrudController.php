<?php

namespace App\Controller\Admin;

use App\Entity\ArchivedEvent;
use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CsvExporterService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ArchivedEventCrudController extends AbstractCrudController
{

    private string $imageDir;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(ParameterBagInterface $params, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->imageDir = $params->get('kernel.project_dir') . '/public/images/evenements/images-evenements/';
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportCsvAction = Action::new('exportArchivedEventsCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_archived_events_csv')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary');

        return $actions
            ->disable(Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $exportCsvAction)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setLabel('Fiche')
                    ->setIcon('fa fa-calendar-times')
                    ->addCssClass('btn btn-info');
            });
    }

    public static function getEntityFqcn(): string
    {
        return ArchivedEvent::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('event')->setLabel('Événement source'))
            ->add(DateTimeFilter::new('archivedAt')->setLabel('Date d’archivage'))
            ->add(BooleanFilter::new('draft')->setLabel('Événement archivé en brouillon'));
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Événement archivé')
            ->setEntityLabelInPlural('Événements archivés')
            ->setDefaultSort(['archivedAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {

        $filesystem = new Filesystem();

        $imageChoices = [];
        if ($filesystem->exists($this->imageDir)) {
            $images = array_diff(scandir($this->imageDir), ['.', '..']);
            $imageChoices = array_combine($images, $images);
        }

        return [
            FormField::addTab('Retour sur image associé')->setIcon('fas fa-image'),
            AssociationField::new('event', 'Événement source')
                ->hideWhenUpdating()
                ->hideWhenCreating()
                ->setRequired(true)
                ->setFormTypeOptions([
                    'choice_label' => function (Event $event) {
                        return sprintf(
                            '%s (%s - %s)',
                            $event->getTitleEvent(),
                            $event->getTypeEvent(),
                            $event->getDateEvent()?->format('d/m/Y')
                        );
                    }
                ]),
            AssociationField::new('backToImage', 'Retour sur image associée')
                ->setRequired(false)
                ->setHelp('<small>Permet de lier un retour sur image à cet événement archivé.<br>Identifiez facilement le bon retour grâce à son numéro (ID) affiché dans la liste déroulante.</small>'),

            ImageField::new('event.imgEvent', 'Image de fond de l\'événement')
                ->setBasePath('images/evenements/images-evenements/')
                ->onlyOnIndex(),

            TextField::new('shortFilmsList', 'Courts-métrages diffusés')
                ->hideOnForm()
                ->setVirtual(true)
                ->formatValue(fn($value, $entity) => $entity->getShortFilmsAsHtmlList())
                ->renderAsHtml(),

            TextField::new('speakersList', 'Jurys')
                ->hideOnForm()
                ->setVirtual(true)
                ->formatValue(fn($value, $entity) => $entity->getSpeakersAsHtmlList())
                ->renderAsHtml(),

            TextField::new('participantsAsHtmlList', 'Participants')
                ->hideOnForm()
                ->setVirtual(true)
                ->formatValue(fn($value, $entity) => $entity->getParticipantsAsHtmlList())
                ->renderAsHtml(),

            DateTimeField::new('archivedAt', 'Date d\'archivage')
                ->hideWhenUpdating()
                ->hideWhenCreating(),

            FormField::addTab('Brouillon')->setIcon('fas fa-pencil-alt'),
            BooleanField::new('draft', 'Brouillon')
                ->setHelp('<small>Si activé, l’événement archivé ne sera pas visible sur le site public. Utilisé pour les fiches en cours de rédaction ou à valider.</small>'),
        ];
    }

    #[Route('/admin/export-archived-events-csv', name: 'export_archived_events_csv')]
    public function exportArchivedEventsCsv(CsvExporterService $csvExporter, EntityManagerInterface $em): Response
    {
        $archivedEvents = $em->getRepository(ArchivedEvent::class)->findAll();

        $data = [];

        foreach ($archivedEvents as $archived) {
            $event = $archived->getEvent();

            $shortFilms = $event?->getShortFilms()?->map(fn($f) => $f->getTitleShortFilm()) ?? [];
            $shortFilmsString = count($shortFilms) > 0 ? implode("\n", $shortFilms->toArray()) : 'Aucun';

            $speakers = $event?->getSpeakers()?->map(fn($s) => (string) $s) ?? [];
            $speakersString = count($speakers) > 0 ? implode("\n", $speakers->toArray()) : 'Aucun';

            $participants = $event?->getUserEvents() ?? [];
            $participantsString = 'Aucun';

            if (count($participants) > 0) {
                $formatted = [];
                foreach ($participants as $userEvent) {
                    $user = $userEvent->getUser();
                    $formatted[] = sprintf(
                        "%s %s\n%s\n%s",
                        $user->getFirstnameUser(),
                        $user->getLastnameUser(),
                        $user->getEmail(),
                        $user->getTelephoneUser()
                    );
                }
                $participantsString = implode("\n\n", $formatted);
            }

            $data[] = [
                $event?->getTitleEvent() ?? 'N/A',
                $event?->getNumberEdition() ?? 'N/A',
                $event?->getTypeEvent() ?? 'N/A',
                $event?->getDateEvent()?->format('d/m/Y') ?? 'N/A',
                $event?->getLocation()?->__toString() ?? 'N/A',
                $event?->isFree() ? 'Gratuit' : ($event?->getPriceEvent() ?: 'Non précisé'),
                $shortFilmsString,
                $speakersString,
                $participantsString,
                $archived->getArchivedAt()?->format('d/m/Y') ?? 'N/A',
                $archived->isDraft() ? 'Brouillon' : 'Publiée',
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
            'Date d\'archivage',
            'État de publication',
        ];

        return $csvExporter->export($data, $headers, 'evenements_archives.csv');
    }
}
