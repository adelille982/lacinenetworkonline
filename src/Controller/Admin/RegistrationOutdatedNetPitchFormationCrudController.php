<?php

namespace App\Controller\Admin;

use App\Entity\RegistrationNetPitchFormation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CsvExporterService;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class RegistrationOutdatedNetPitchFormationCrudController extends AbstractCrudController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return RegistrationNetPitchFormation::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('firstnameRegistration', 'Prénom'))
            ->add(TextFilter::new('lastnameRegistration', 'Nom'))
            ->add(TextFilter::new('emailRegistration', 'Email'))
            ->add(TextFilter::new('telRegistration', 'Téléphone'))
            ->add(TextFilter::new('professionalProjectRegistration', 'Projet professionnel'))
            ->add(TextFilter::new('cvRegistration', 'Nom du fichier CV'))
            ->add(EntityFilter::new('sessionNetPitchFormation', 'Session'))
            ->add(DateTimeFilter::new('createdAtRegistration', 'Date d’inscription'))
            ->add(ChoiceFilter::new('statutRegistration', 'Statut')->setChoices([
                'En cours' => 'En cours',
                'Validé' => 'Validé',
            ]));
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Inscription obsolète')
            ->setEntityLabelInPlural('Inscriptions obsolètes')
            ->setDefaultSort(['createdAtRegistration' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {

        $exportCsvAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_registration_outdated_csv')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary')
            ->setIcon('fa fa-download');

        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $exportCsvAction);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, $fields, $filters): QueryBuilder
    {
        $now = new \DateTime();

        return $this->entityManager
            ->getRepository(RegistrationNetPitchFormation::class)
            ->createQueryBuilder('r')
            ->join('r.sessionNetPitchFormation', 's')
            ->andWhere('r.statutRegistration = :status')
            ->andWhere('s.endDateSessionNetPitchFormation < :now')
            ->setParameter('status', 'En cours')
            ->setParameter('now', $now);

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('cvRegistration', 'CV')
                ->formatValue(function ($value) {
                    if (!$value) {
                        return '<span style="color: gray;">Aucun fichier</span>';
                    }

                    $filename = basename($value);

                    return sprintf(
                        '<a href="/images/cv/cv-inscriptions-formations/%s" target="_blank" class="btn btn-sm btn-primary">📄 Voir le CV</a>',
                        htmlspecialchars($filename)
                    );
                })
                ->renderAsHtml(),
            TextField::new('lastnameRegistration', 'Nom'),
            TextField::new('firstnameRegistration', 'Prénom'),
            TextField::new('emailRegistration', 'Email'),
            TextField::new('telRegistration', 'Téléphone'),
            AssociationField::new('sessionNetPitchFormation', 'Session'),
            TextEditorField::new('professionalProjectRegistration', 'Projet professionnel'),
            ChoiceField::new('statutRegistration', 'Statut')
                ->setChoices([
                    'En cours' => 'En cours',
                    'Validé' => 'Validé',
                ]),
            DateTimeField::new('createdAtRegistration', 'Date d’inscription'),
        ];
    }

    #[Route('/export-registration-outdated-csv', name: 'export_registration_outdated_csv')]
    public function exportCsv(CsvExporterService $csvExporter): Response
    {
        $now = new \DateTime();

        $registrations = $this->entityManager
            ->getRepository(RegistrationNetPitchFormation::class)
            ->createQueryBuilder('r')
            ->join('r.sessionNetPitchFormation', 's')
            ->andWhere('r.statutRegistration = :status')
            ->andWhere('s.endDateSessionNetPitchFormation < :now')
            ->setParameter('status', 'En cours')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        $data = [];

        foreach ($registrations as $registration) {
            $data[] = [
                $registration->getFirstnameRegistration(),
                $registration->getLastnameRegistration(),
                $registration->getEmailRegistration(),
                $registration->getTelRegistration(),
                $registration->getSessionNetPitchFormation()?->getNetPitchFormation()?->getTitleNetPitchFormation() ?? 'N/A',
                $registration->getSessionNetPitchFormation()?->getStartDateSessionNetPitchFormation()?->format('d/m/Y') ?? 'N/A',
                $registration->getSessionNetPitchFormation()?->getEndDateSessionNetPitchFormation()?->format('d/m/Y') ?? 'N/A',
                $registration->getCreatedAtRegistration()?->format('Y-m-d H:i:s'),
                $registration->getProfessionalProjectRegistration(),
            ];
        }

        $headers = [
            'Prénom',
            'Nom',
            'Email',
            'Téléphone',
            'Formation',
            'Date de début',
            'Date de fin',
            'Date d\'inscription',
            'Nom du CV',
            'Projet professionnel',
        ];

        return $csvExporter->export($data, $headers, 'inscriptions_obsoletes.csv');
    }
}
