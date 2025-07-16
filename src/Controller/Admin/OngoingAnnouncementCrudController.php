<?php

namespace App\Controller\Admin;

use App\Entity\Announcement;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class OngoingAnnouncementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Announcement::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $today = new \DateTimeImmutable('today');

        $qb->andWhere('entity.availabilityAnnouncement <= :today')
           ->andWhere('entity.expiryAnnouncement >= :today')
           ->setParameter('today', $today);

        return $qb;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setLabel('Fiche')
                    ->setIcon('fas fa-book-open')
                    ->addCssClass('btn btn-info');
            });
    }

        public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('typeAnnouncement', 'Type d\'annonce'))
            ->add(TextFilter::new('departmentAnnouncement', 'Département'))
            ->add(TextFilter::new('cityAnnouncement', 'Ville'))
            ->add(TextFilter::new('linkAnnouncement', 'Email de contact'))
            ->add(BooleanFilter::new('remuneration', 'Mission rémunérée ?'))
            ->add(DateTimeFilter::new('createdAtAnnouncement', 'Date de création'))
            ->add(EntityFilter::new('subCategoryAnnouncement', 'Métier associé'))
            ->add(EntityFilter::new('user', 'Utilisateur'));
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Annonces en cours')
            ->setEntityLabelInSingular('Annonce en cours')
            ->setDefaultSort(['expiryAnnouncement' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('user', 'Nom complet de l\'utilisateur'),
            TextField::new('typeAnnouncement', 'Type'),
            AssociationField::new('subCategoryAnnouncement', 'Métier recherché / Proposé'),
            TextField::new('departmentAnnouncement', 'Département'),
            TextField::new('cityAnnouncement', 'Ville'),
            DateTimeField::new('availabilityAnnouncement', 'Début')
                ->setFormat('dd/MM/yyyy')
                ->setTimezone('Europe/Paris'),

            DateTimeField::new('expiryAnnouncement', 'Fin')
                ->setFormat('dd/MM/yyyy')
                ->setTimezone('Europe/Paris')
                ->formatValue(function ($value) {
                    if (!$value instanceof \DateTimeInterface) {
                        return '<span style="color:gray;">—</span>';
                    }
                    return sprintf('<span style="color:orange;">%s</span>', $value->format('d/m/Y'));
                }),

            TextEditorField::new('textAnnouncement', 'Description'),
            BooleanField::new('remuneration', 'Mission rémunérée')
                ->renderAsSwitch(false)
                ->formatValue(fn($value) => $value ? 'Oui' : 'Non'),

            TextField::new('linkAnnouncement', 'Email de contact')
                ->formatValue(function ($value) {
                    if (!$value) return '—';
                    return sprintf('<a href="mailto:%s">%s</a>', htmlspecialchars($value), htmlspecialchars($value));
                }),

            DateTimeField::new('createdAtAnnouncement', 'Date de création')
                ->onlyOnIndex()
                ->setTimezone('Europe/Paris'),
        ];
    }
}
