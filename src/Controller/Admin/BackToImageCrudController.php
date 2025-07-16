<?php

namespace App\Controller\Admin;

use App\Entity\BackToImage;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BackToImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BackToImage::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setLabel('Fiche')
                    ->setIcon('fa fa-file-alt')
                    ->addCssClass('btn btn-info');
            });
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Retour sur image')
            ->setEntityLabelInPlural('Retours sur image')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Contenu')->setIcon('fa fa-align-left'),
            IdField::new('id')
                ->hideOnForm()
                ->setLabel('N°'),

            TextEditorField::new('textBackToImage')
                ->setLabel('Texte du retour sur image')
                ->formatValue(fn($value) => strip_tags($value))
                ->setHelp(
                    '<span style="color: red;">
        Ce texte accompagne le retour sur image d’un événement archivé. Il doit être clair, structuré et illustratif.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Conseils pour rédiger ce texte (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Rédigez d’abord sur <strong>Word ou Google Docs</strong> (titres H1/H2, paragraphes), puis copiez-collez ici.</li>
                    <li>Mettez en valeur les temps forts, les émotions ou le bilan de l’événement.</li>
                    <li>Utilisez des phrases courtes et un ton engageant.</li>
                    <li>Adaptez le contenu au public cible (professionnels, partenaires, spectateurs, etc.).</li>
                    <li>Ajoutez des anecdotes ou citations si pertinent.</li>
                </ul>
                <p><em>Ce texte sert à transmettre une impression durable de l’événement passé. Faites-le vivant et impactant.</em></p>
            </div>
        </details>'
                ),

            FormField::addTab('Images associées')->setIcon('fa fa-image'),
            AssociationField::new('imageBackToImages')
                ->setLabel('Images')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'multiple' => true,
                ])
                ->setRequired(false)
                ->hideOnDetail()
                ->hideOnIndex()
                ->setHelp('<small>Ajoutez de nouvelles images directement depuis le menu "Images des retours sur image".<br>Une fois ajoutées, elles apparaîtront ici et pourront être associées à ce retour sur image.</small>'),

            TextField::new('imagesPreviewAsHtml', 'Images associées')
                ->hideOnForm()
                ->setVirtual(true)
                ->renderAsHtml(),

            AssociationField::new('archivedEvent')
                ->setLabel('Événement lié')
                ->setRequired(false)
                ->hideWhenCreating()
                ->hideWhenUpdating(),
        ];
    }
}
