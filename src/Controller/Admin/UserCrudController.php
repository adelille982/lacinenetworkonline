<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CsvExporterService;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class UserCrudController extends AbstractCrudController
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('lastnameUser', 'Nom de famille'))
            ->add(TextFilter::new('firstnameUser', 'Prénom'))
            ->add(TextFilter::new('email', 'Adresse email'))
            ->add(TextFilter::new('telephoneUser', 'Numéro de téléphone'))
            ->add(ArrayFilter::new('roles', 'Rôle attribué'))
            ->add(BooleanFilter::new('isVerified', 'Email vérifié'))
            ->add(TextFilter::new('fieldOfEvolutionUser', 'Domaine d\'évolution'))
            ->add(BooleanFilter::new('intermittentUser', 'Statut intermittent'))
            ->add(DateTimeFilter::new('createdAtUser', 'Date d\'inscription'))
            ->add(DateTimeFilter::new('conditionValidated', 'Date de validation des conditions'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportCsvAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_users_csv')
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
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(30);
    }

    public function configureFields(string $pageName): iterable
    {
        $filesystem = new Filesystem();
        $imageDir = $this->projectDir . '/public/images/photos-des-utilisateurs';
        $cvDir = $this->projectDir . '/public/images/cv/cv-utilisateurs/';

        $imageChoices = [];
        $cvChoices = [];

        if ($filesystem->exists($imageDir)) {
            $images = array_diff(scandir($imageDir), ['.', '..']);
            $imageChoices = array_combine($images, $images);
        }

        if ($filesystem->exists($cvDir)) {
            $pdfs = array_filter(scandir($cvDir), fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'pdf');
            $cvChoices = array_combine($pdfs, $pdfs);
        }

        return array_filter([
            FormField::addTab('Informations personnelles')->setIcon('fas fa-user'),
            ChoiceField::new('pictureUser', 'Photo de profil')
                ->setChoices($imageChoices)
                ->onlyOnForms()
                ->setHelp('<small>Sélectionnez une image existante dans <code>/photos-des-utilisateurs</code>. Format conseillé : WebP ou JPG, portrait.</small>'),

            // Image affichée sur index
            ImageField::new('pictureUser', 'Photo de profil')
                ->setBasePath('/images/photos-des-utilisateurs')
                ->onlyOnIndex(),

            // Image affichée sur page détail
            ImageField::new('pictureUser', 'Photo de profil')
                ->setBasePath('/images/photos-des-utilisateurs')
                ->onlyOnDetail(),


            TextField::new('lastnameUser', 'Nom'),
            TextField::new('firstnameUser', 'Prénom'),
            EmailField::new('email', 'Email'),
            TextField::new('telephoneUser', 'Téléphone'),
            TextField::new('curriculumUser', 'CV')
                ->onlyOnIndex()
                ->onlyOnDetail()
                ->formatValue(function ($value) {
                    if (!$value) {
                        return '<span style="color: gray;">Aucun fichier</span>';
                    }

                    $filename = basename($value);

                    return sprintf(
                        '<a href="/images/cv/cv-utilisateurs/%s" target="_blank" class="btn btn-sm btn-primary">📄 Voir le CV</a>',
                        htmlspecialchars($filename)
                    );
                })
                ->renderAsHtml(),

            ChoiceField::new('curriculumUser', 'Fichier CV (PDF)')
                ->setChoices($cvChoices)
                ->setRequired(false)
                ->onlyOnForms(),

            FormField::addTab('Sécurité et rôles')->setIcon('fas fa-lock'),
            TextField::new('password', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->onlyWhenCreating()
                ->setRequired(true),

            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Utilisateur Network' => 'ROLE_USER_NETWORK',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(false),
            BooleanField::new('isVerified', 'Profil vérifié'),

            FormField::addTab('Informations complémentaires')->setIcon('fas fa-info-circle'),
            TextareaField::new('fieldOfEvolutionUser', 'Domaine d\'évolution')->hideOnIndex(),
            BooleanField::new('intermittentUser', 'Intermittent / Cachets / AFDAS')->renderAsSwitch(true),
            DateTimeField::new('createdAtUser', 'Date de création')->hideOnForm(),
            DateTimeField::new('conditionValidated', 'Date de validation')
                ->onlyOnIndex()
                ->onlyOnDetail()
                ->formatValue(function ($value) {
                    if (!$value instanceof \DateTimeInterface) {
                        return '<span style="color: gray;">—</span>';
                    }

                    $now = new \DateTimeImmutable();
                    $diff = \DateTimeImmutable::createFromInterface($value)->diff($now)->days;
                    $isPast = $now > \DateTimeImmutable::createFromInterface($value)->modify('+1 year');

                    $color = match (true) {
                        $isPast => 'red',
                        $diff >= 180 => 'orange',  // entre 6 et 12 mois
                        default => 'green',        // moins de 6 mois
                    };

                    return sprintf('<span style="color:%s;">%s</span>', $color, $value->format('d/m/Y'));
                }),
        ]);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $qb->andWhere('entity.isVerified = true')
            ->andWhere($qb->expr()->like('entity.roles', ':roleUserNetwork'))
            ->setParameter('roleUserNetwork', '%"ROLE_USER_NETWORK"%');
    }

    #[Route('/export-users-csv', name: 'export_users_csv')]
    public function exportCsv(CsvExporterService $csvExporter, ManagerRegistry $doctrine): Response
    {
        $users = $doctrine->getRepository(User::class)->findBy(['isVerified' => true]);

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                $user->getFirstnameUser(),
                $user->getLastnameUser(),
                $user->getEmail(),
                $user->getTelephoneUser(),
                implode(', ', $user->getRoles()),
                $user->isVerified() ? 'Oui' : 'Non',
                $user->getCreatedAtUser()?->format('Y-m-d H:i:s'),
                $user->getFieldOfEvolutionUser(),
                $user->getIntermittentUser(),
            ];
        }

        // Définir les en-têtes du fichier CSV
        $headers = ['Prénom', 'Nom', 'Email', 'Téléphone', 'Rôles', 'Profil vérifié', 'Créé le', 'Domaine d\'évolution', 'Intermittent'];

        return $csvExporter->export($data, $headers, 'export-utilisateurs.csv');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) return;

        // Définir la date uniquement lors de la création
        $entityInstance->setConditionValidated(new DateTimeImmutable());

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) return;

        // Ne pas toucher à conditionValidated ici
        parent::updateEntity($entityManager, $entityInstance);
    }
}
