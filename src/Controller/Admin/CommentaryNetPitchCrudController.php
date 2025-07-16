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

class CommentaryNetPitchCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commentary::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
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
            ->setEntityLabelInPlural('Commentaires sur les formations')
            ->setEntityLabelInSingular('Commentaire sur la formation')
            ->setDefaultSort(['createdAt' => 'DESC'])
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

        return $qb
            ->andWhere('entity.netPitchFormation IS NOT NULL')
            ->andWhere('entity.archivedEvent IS NULL');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Auteur du commentaire')->setIcon(''),
            AssociationField::new('user', 'Auteur')->hideWhenUpdating(),

            FormField::addTab('Texte du commentaire')->setIcon(''),
            TextEditorField::new('textCommentary', 'Commentaire')->hideWhenUpdating(),

            FormField::addTab('Formation associée')->setIcon(''),
            AssociationField::new('netPitchFormation', 'Formation')
                ->hideWhenUpdating(),

            ChoiceField::new('statutCommentary', 'Statut')
                ->setChoices([
                    'Validé' => 'validé',
                    'En attente' => 'en attente',
                ])
                ->hideWhenUpdating()
                ->hideWhenCreating(),

            DateTimeField::new('createdAt', 'Créé le')->hideWhenUpdating()
                ->hideWhenCreating(),
        ];
    }
}
