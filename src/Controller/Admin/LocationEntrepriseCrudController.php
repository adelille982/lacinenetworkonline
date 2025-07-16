<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Doctrine\ORM\QueryBuilder;

class LocationEntrepriseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Location::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Lieux Entreprise')
            ->setEntityLabelInPlural('Lieux Entreprises')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, $fields, $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $qb->andWhere('entity.typeLocation = :type')
            ->setParameter('type', 'Entreprise');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('streetLocation', 'Adresse'),
            TextField::new('postalCode', 'Code postal'),
            TextField::new('cityLocation', 'Ville'),
            TextField::new('typeLocation', 'Type de lieu')->hideOnForm(), // pour info uniquement
        ];
    }

    public function createEntity(string $entityFqcn)
    {
        $location = new Location();
        $location->setTypeLocation('Entreprise');
        return $location;
    }
}
