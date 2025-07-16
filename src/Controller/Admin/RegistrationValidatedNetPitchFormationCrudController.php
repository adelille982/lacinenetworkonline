<?php

namespace App\Controller\Admin;

use App\Entity\RegistrationNetPitchFormation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CsvExporterService;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class RegistrationValidatedNetPitchFormationCrudController extends AbstractCrudController
{
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
            ->add(TextFilter::new('cvRegistration', 'Nom du CV'))
            ->add(ChoiceFilter::new('afdas', 'Éligible AFDAS')
                ->setChoices([
                    'Oui' => true,
                    'Non' => false,
                ]))
            ->add(TextFilter::new('professionalProjectRegistration', 'Projet professionnel'))
            ->add(EntityFilter::new('sessionNetPitchFormation', 'Session liée'))
            ->add(ChoiceFilter::new('statutRegistration', 'Statut')->setChoices([
                'En cours' => 'En cours',
                'Validé' => 'Validé',
            ]))
            ->add(DateTimeFilter::new('createdAtRegistration', 'Date d’inscription'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $cancelationAction = Action::new('cancelation', 'Mettre en attente')
            ->linkToCrudAction('cancelationRegistration')
            ->displayIf(static function ($entity) {
                return $entity->getStatutRegistration() === 'Validé';
            })
            ->setIcon('fa fa-clock')
            ->addCssClass('btn btn-warning');

        $exportCsvAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_registration_validated_csv')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary')
            ->setIcon('fa fa-download');

        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $exportCsvAction)
            ->add(Crud::PAGE_INDEX, $cancelationAction);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Inscription validée')
            ->setEntityLabelInPlural('Inscriptions validées')
            ->setDefaultSort(['createdAtRegistration' => 'DESC']);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, $fields, $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $now = new \DateTime();

        return $qb
            ->join('entity.sessionNetPitchFormation', 'session')
            ->andWhere('entity.statutRegistration = :status')
            ->andWhere('session.startDateSessionNetPitchFormation > :now')
            ->setParameter('status', 'Validé')
            ->setParameter('now', $now);
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
            TextField::new('firstnameRegistration', 'Prénom'),
            TextField::new('lastnameRegistration', 'Nom'),
            TextField::new('emailRegistration', 'Email'),
            TextField::new('telRegistration', 'Téléphone'),

            BooleanField::new('afdas', 'Éligible AFDAS'),

            TextEditorField::new('professionalProjectRegistration', 'Projet professionnel'),

            AssociationField::new('sessionNetPitchFormation', 'Session')->onlyOnIndex(),

            ChoiceField::new('statutRegistration', 'Statut')
                ->setChoices([
                    'En cours' => 'En cours',
                    'Validé' => 'Validé',
                ]),

            DateTimeField::new('createdAtRegistration', 'Date d’inscription'),
        ];
    }

    public function cancelationRegistration(
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator
    ): RedirectResponse {
        $request = $requestStack->getCurrentRequest();
        $id = $request->query->get('entityId');

        $registration = $entityManager->getRepository(RegistrationNetPitchFormation::class)->find($id);

        if ($registration) {
            $registration->setStatutRegistration('En cours');
            $entityManager->flush();
            $this->addFlash('success', 'Inscription mise en attente avec succès.');
        } else {
            $this->addFlash('danger', 'Inscription introuvable.');
        }

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction('index')
            ->generateUrl();

        return new RedirectResponse($url);
    }

    #[Route('/export-registration-validated-csv', name: 'export_registration_validated_csv')]
    public function exportCsv(CsvExporterService $csvExporter, EntityManagerInterface $entityManager): Response
    {
        $now = new \DateTime();

        $registrations = $entityManager->createQueryBuilder()
            ->select('r')
            ->from(RegistrationNetPitchFormation::class, 'r')
            ->join('r.sessionNetPitchFormation', 's')
            ->where('r.statutRegistration = :status')
            ->andWhere('s.startDateSessionNetPitchFormation > :now')
            ->setParameter('status', 'Validé')
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
                $registration->isAfdas() ? 'Oui' : 'Non',
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
            'Éligible AFDAS',
            'Formation',
            'Date de début',
            'Date de fin',
            'Date d\'inscription',
            'Nom du CV',
            'Projet professionnel',
        ];

        return $csvExporter->export($data, $headers, 'inscriptions_validees.csv');
    }
}
