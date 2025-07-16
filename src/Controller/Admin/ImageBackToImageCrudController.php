<?php

namespace App\Controller\Admin;

use App\Entity\ImageBackToImage;
use Doctrine\ORM\Mapping\Id;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class ImageBackToImageCrudController extends AbstractCrudController
{

    private string $imageDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->imageDir = $params->get('kernel.project_dir') . '/public/images/evenements/retours-sur-images/';
    }

    public static function getEntityFqcn(): string
    {
        return ImageBackToImage::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::EDIT);
    }

    public function configureFields(string $pageName): iterable
    {
        $filesystem = new Filesystem();

        $imageChoices = [];
        if ($filesystem->exists($this->imageDir)) {
            $images = array_diff(scandir($this->imageDir), ['.', '..']);
            $imageChoices = array_combine($images, $images);
        }

        return [
            IdField::new('id')
                ->hideOnForm()
                ->setLabel('N°'),
            ChoiceField::new('imgBackToImage', 'Image')
                ->setChoices($imageChoices)
                ->setFormTypeOption('empty_data', '')
                ->setRequired(false)
                ->onlyOnForms()
                ->setHelp('<small>Pour qu’une image apparaisse dans ce sélecteur, elle doit être préalablement ajoutée dans le dossier <code>/événements/retours-sur-images</code>.</small>'),

            ImageField::new('imgBackToImage', 'Sélectionner une image')
                ->setBasePath('images/evenements/retours-sur-images/')
                ->onlyOnIndex(),

            AssociationField::new('backToImage', 'Retour sur image')
                ->formatValue(function ($value, $entity) {
                    if ($value && $value->getArchivedEvent() && $value->getArchivedEvent()->getEvent()) {
                        $event = $value->getArchivedEvent()->getEvent();
                        return sprintf(
                            '%s (%s)',
                            $event->getTitleEvent(),
                            $event->getDateEvent()?->format('d/m/Y') ?? ''
                        );
                    }
                    return 'Non lié';
                })
                ->onlyOnIndex()
                ->hideWhenCreating()
                ->hideWhenUpdating(),
        ];
    }
}
