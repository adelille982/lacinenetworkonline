<?php

namespace App\Controller\Admin;

use App\Controller\Admin\NetPitchFormationCrudController;
use App\Entity\Gain;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class GainCrudController extends AbstractCrudController
{
    private string $imageDir;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(ParameterBagInterface $params, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->imageDir = $params->get('kernel.project_dir') . '/public/images/formation/gain/';
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Gain::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Gain')
            ->setEntityLabelInPlural('Gains')
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('titleGain')
            ->add('sloganGain');
    }

    public function configureActions(Actions $actions): Actions
    {
        $formLinkAction = Action::new('linkGain', 'Visité le lien', 'fa fa-link')
            ->linkToUrl(function ($entityInstance) {
                return $entityInstance->getLinkGain() ?: '#';
            })
            ->setHtmlAttributes([
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
            ]);

        return $actions
            ->add(Crud::PAGE_INDEX, $formLinkAction);
    }

    public function configureFields(string $pageName): iterable
    {
        $filesystem = new Filesystem();
        $choices = [];

        if ($filesystem->exists($this->imageDir)) {
            $images = array_diff(scandir($this->imageDir), ['.', '..']);
            $choices = array_combine($images, $images);
        }

        return [
            FormField::addTab('Informations du gain')->setIcon('fas fa-info-circle'),
            TextField::new('titleGain', 'Titre')
                ->setRequired(true)
                ->setHelp('Utilisez un titre clair et précis.'),
            TextField::new('sloganGain', 'Slogan')
                ->setRequired(false)
                ->setHelp('Un slogan court qui décrit brièvement le gain.'),
            TextField::new('linkGain', 'Lien (URL)')
                ->hideOnIndex()
                ->setHelp('Laisser vide si aucun lien n’est associé.'),

            FormField::addTab('Image du gain')->setIcon('fas fa-image'),
            ChoiceField::new('imgGain', 'Sélectionner une image pour le gain')
                ->setChoices($choices)
                ->setFormTypeOption('empty_data', '')
                ->setRequired(true)
                ->onlyOnForms()
                ->setHelp('<small>
        Sélectionnez une image déjà présente dans le dossier <code>/formation/gain/</code>.<br>
        Cette image apparaîtra sur les fiches formations. <br>
        <strong>Format conseillé :</strong> JPG ou WebP, format paysage.
    </small>'),
            ImageField::new('imgGain', 'Image du gain')
                ->setBasePath('images/formation/gain/')
                ->onlyOnIndex(),

            AssociationField::new('netPitchFormations', 'Formations associées')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->formatValue(function ($value) {
                    if ($value instanceof \Doctrine\Common\Collections\Collection) {
                        $adminUrlGenerator = $this->adminUrlGenerator;

                        return implode('<br>', $value->map(function ($formation) use ($adminUrlGenerator) {
                            $url = $adminUrlGenerator
                                ->setController(NetPitchFormationCrudController::class)
                                ->setAction('detail')
                                ->setEntityId($formation->getId())
                                ->generateUrl();

                            return sprintf('<a href="%s">%s</a>', $url, (string) $formation);
                        })->toArray());
                    }
                    return '';
                })
                ->renderAsHtml()
                ->setHelp('Cliquez sur une formation pour accéder à sa fiche détail EasyAdmin.')
        ];
    }
}
