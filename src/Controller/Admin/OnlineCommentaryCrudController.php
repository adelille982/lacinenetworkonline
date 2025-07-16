<?php

namespace App\Controller\Admin;

use App\Entity\Commentary;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CsvExporterService;
use Doctrine\Persistence\ManagerRegistry;

class OnlineCommentaryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commentary::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportAction = Action::new('exportCsv', 'Exporter en CSV', 'fa fa-download')
            ->linkToRoute('export_validated_comments')
            ->createAsGlobalAction()
            ->addCssClass('btn btn-secondary')
            ->setIcon('fa fa-download');

        return $actions
            ->add(Crud::PAGE_INDEX, $exportAction)
            ->disable(Action::EDIT, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
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
            ->setEntityLabelInPlural('Commentaires validés')
            ->setEntityLabelInSingular('Commentaire validé')
            ->setDefaultSort(['validatedAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(
                EntityFilter::new('user', 'Auteur du commentaire')
            )
            ->add(
                TextFilter::new('textCommentary', 'Contenu du commentaire')
                    ->setFormTypeOption('attr', ['placeholder' => 'Recherche dans le texte...'])
            )
            ->add(
                EntityFilter::new('netPitchFormation', 'Formation associée')
            )
            ->add(
                EntityFilter::new('archivedEvent', 'Événement archivé')
            )
            ->add(
                ChoiceFilter::new('statutCommentary', 'Statut du commentaire')
                    ->setChoices([
                        'Validé' => 'validé',
                        'En attente' => 'en attente',
                    ])
            )
            ->add(
                DateTimeFilter::new('createdAt', 'Date de création')
            );
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $qb->andWhere('entity.statutCommentary = :status')
            ->setParameter('status', 'validé');
    }

    #[Route('/admin/export-commentary-valides', name: 'export_validated_comments')]
    public function exportCsv(CsvExporterService $csvExporter, ManagerRegistry $doctrine): Response
    {
        $comments = $doctrine->getRepository(Commentary::class)->findBy(['statutCommentary' => 'validé']);

        $data = [];
        foreach ($comments as $comment) {
            $data[] = [
                'Auteur' => $comment->getUser()?->getFullName() ?? '—',
                'Texte du commentaire' => $comment->getTextCommentary(),
                'Formation' => $comment->getNetPitchFormation()?->getTitleNetPitchFormation() ?? '—',
                'Evénement archivié' => $comment->getArchivedEvent()?->getEvent()?->getTitleEvent() ?? '—',
                'Créé le' => $comment->getCreatedAt()?->format('d/m/Y H:i') ?? '—',
                'Validé le' => $comment->getValidatedAt()?->format('d/m/Y H:i') ?? '—',
            ];
        }

        $headers = ['Auteur', 'Texte du commentaire', 'Formation', 'Evénement archivié', 'Créé le', 'Validé le'];

        return $csvExporter->export($data, $headers, 'export-commentaires-valides.csv');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Auteur du commentaire')->setIcon(''),
            AssociationField::new('user', 'Auteur')->hideWhenUpdating(),

            FormField::addTab('Texte du commentaire')->setIcon(''),
            TextEditorField::new('textCommentary', 'Commentaire')->hideWhenUpdating(),

            FormField::addTab('Événement ou formation associé au commentaire')->setIcon(''),
            AssociationField::new('archivedEvent', 'Événement (archivé)')
                ->hideWhenUpdating(),
            AssociationField::new('netPitchFormation', 'Formation')
                ->hideWhenUpdating(),

            ChoiceField::new('statutCommentary', 'Statut')
                ->setChoices([
                    'Validé' => 'validé',
                ])
                ->hideWhenUpdating()
                ->hideWhenCreating(),

            DateTimeField::new('createdAt', 'Créé le')->hideWhenUpdating()
                ->hideWhenCreating(),
            DateTimeField::new('validatedAt', 'Validé le')->hideWhenUpdating()
                ->hideWhenCreating(),
        ];
    }
}
