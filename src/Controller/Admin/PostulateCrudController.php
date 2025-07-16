<?php

namespace App\Controller\Admin;

use App\Entity\Postulate;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class PostulateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Postulate::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Candidature')
            ->setEntityLabelInPlural('Candidatures reçues')
            ->setDefaultSort(['conditionValidated' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('firstname'))
            ->add(TextFilter::new('lastname'))
            ->add(TextFilter::new('email'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('curiculum', 'CV')
                ->formatValue(function ($value) {
                    if (!$value) {
                        return '<span style="color: gray;">Aucun fichier</span>';
                    }

                    $filename = basename($value);

                    return sprintf(
                        '<a href="/images/cv/cv-cine-network/%s" target="_blank" class="btn btn-sm btn-primary">📄 Voir le CV</a>',
                        htmlspecialchars($filename)
                    );
                })
                ->renderAsHtml(),
            TextField::new('firstname', 'Prénom'),
            TextField::new('lastname', 'Nom'),
            TextField::new('email', 'Adresse email'),
            TextField::new('telephone', 'Téléphone'),
            TextEditorField::new('professionalExperience', 'Expérience professionnelle'),
            DateTimeField::new('conditionValidated', 'Date de soumission')->setFormat('dd/MM/yyyy à HH:mm'),
        ];
    }
}
