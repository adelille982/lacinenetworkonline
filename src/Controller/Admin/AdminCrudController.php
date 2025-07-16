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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class AdminCrudController extends AbstractCrudController
{
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
            ->add(ArrayFilter::new('roles', 'Rôle attribué'))
            ->add(BooleanFilter::new('isVerified', 'Email vérifié'))
            ->add(DateTimeFilter::new('createdAtUser', 'Date d\'inscription'));
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Administrateur')
            ->setEntityLabelInPlural('Administrateurs')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(15);
    }

    public function configureFields(string $pageName): iterable
    {
        return array_filter([
            FormField::addTab('Informations personnelles')->setIcon('fas fa-user'),
            TextField::new('lastnameUser', 'Nom de famille'),
            TextField::new('firstnameUser', 'Prénom'),
            TextField::new('email', 'Email'),

            FormField::addTab('Sécurité et rôles')->setIcon('fas fa-lock'),
            TextField::new('password', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->setRequired(false)
                ->onlyOnForms(),

            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Administrateur' => 'ROLE_ADMIN',
                    'Sous-administrateur global' => 'ROLE_SUB_ADMIN',
                    'Sous-admin Formation' => 'ROLE_SUB_ADMIN_FORMATION',
                    'Sous-admin Annonces' => 'ROLE_SUB_ADMIN_ANNOUNCEMENT',
                    'Sous-admin Événements' => 'ROLE_SUB_ADMIN_EVENT',
                    'Sous-admin Blog' => 'ROLE_SUB_ADMIN_BLOG',
                    'Sous-admin Commentaires' => 'ROLE_SUB_ADMIN_COMMENTARY',
                    'Sous-admin Gestion' => 'ROLE_SUB_ADMIN_GESTION',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(false),

            BooleanField::new('isVerified', 'Profil vérifié'),

            DateTimeField::new('createdAtUser', 'Date de création')->hideOnForm(),
        ]);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $qb->andWhere($qb->expr()->orX(
            $qb->expr()->like('entity.roles', ':admin'),
            $qb->expr()->like('entity.roles', ':subAdmin'),
            $qb->expr()->like('entity.roles', ':subAdminFormation'),
            $qb->expr()->like('entity.roles', ':subAdminAnnouncement'),
            $qb->expr()->like('entity.roles', ':subAdminEvent'),
            $qb->expr()->like('entity.roles', ':subAdminBlog'),
            $qb->expr()->like('entity.roles', ':subAdminCommentary'),
            $qb->expr()->like('entity.roles', ':subAdminGestion')
        ))
            ->setParameter('admin', '%"ROLE_ADMIN"%')
            ->setParameter('subAdmin', '%"ROLE_SUB_ADMIN"%')
            ->setParameter('subAdminFormation', '%"ROLE_SUB_ADMIN_FORMATION"%')
            ->setParameter('subAdminAnnouncement', '%"ROLE_SUB_ADMIN_ANNOUNCEMENT"%')
            ->setParameter('subAdminEvent', '%"ROLE_SUB_ADMIN_EVENT"%')
            ->setParameter('subAdminBlog', '%"ROLE_SUB_ADMIN_BLOG"%')
            ->setParameter('subAdminCommentary', '%"ROLE_SUB_ADMIN_COMMENTARY"%')
            ->setParameter('subAdminGestion', '%"ROLE_SUB_ADMIN_GESTION"%');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) return;

        $entityInstance->setConditionValidated(new DateTimeImmutable());

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) return;

        $entityInstance->setConditionValidated(new DateTimeImmutable());

        parent::updateEntity($entityManager, $entityInstance);
    }
}
