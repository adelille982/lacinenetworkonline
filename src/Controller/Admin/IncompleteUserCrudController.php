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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class IncompleteUserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {

        return $actions
            ->disable(Action::NEW);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('lastnameUser', 'Nom de famille'))
            ->add(TextFilter::new('firstnameUser', 'Prénom'))
            ->add(TextFilter::new('email', 'Adresse email'))
            ->add(TextFilter::new('telephoneUser', 'Numéro de téléphone'))
            ->add(BooleanFilter::new('isVerified', 'Email vérifié'))
            ->add(DateTimeFilter::new('createdAtUser', 'Date d\'inscription'));
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Compte non vérifié')
            ->setEntityLabelInPlural('Comptes non vérifiés')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('lastnameUser', 'Nom')->onlyOnIndex(),
            TextField::new('firstnameUser', 'Prénom')->onlyOnIndex(),
            EmailField::new('email', 'Email')->onlyOnIndex(),
            TextField::new('telephoneUser', 'Téléphone')->onlyOnIndex(),

            FormField::addTab('Sécurité')->setIcon('fas fa-lock'),

            BooleanField::new('isVerified', 'Vérifié'),

            DateTimeField::new('createdAtUser', 'Date de création')->onlyOnIndex(),
        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $qb->andWhere('entity.isVerified = false')
            ->andWhere($qb->expr()->like('entity.roles', ':roleUserNetwork'))
            ->setParameter('roleUserNetwork', '%"ROLE_USER_NETWORK"%');
    }
}
