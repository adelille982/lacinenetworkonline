<?php

namespace App\Controller\Admin;

use App\Entity\HeaderHomeImg;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class HeaderHomeImgCrudController extends AbstractCrudController
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    public static function getEntityFqcn(): string
    {
        return HeaderHomeImg::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $imageDirectory = $this->projectDir . '/public/images/en-tete-de-page/accueil/';
        $filesystem = new Filesystem();
        $images = [];

        if ($filesystem->exists($imageDirectory)) {
            $files = scandir($imageDirectory);
            $images = array_filter($files, fn($file) => preg_match('/\.(jpg|jpeg|png|webp)$/i', $file));
        }

        $imageChoices = array_combine($images, $images);

        return [
            IdField::new('id', 'N°')->onlyOnIndex(),

            FormField::addTab('Image')->setIcon('fas fa-image'),
            ChoiceField::new('imageHeaderHome', 'Sélectionner une image')
                ->setChoices($imageChoices)
                ->onlyOnForms(),

            ImageField::new('imageHeaderHome', 'Aperçu de l\'image')
                ->setBasePath('/images/en-tete-de-page/accueil/')
                ->onlyOnIndex(),

            AssociationField::new('headerHome', 'En tête de page associée')
                ->setRequired(true)
                ->hideWhenCreating()
                ->hideWhenUpdating(),

        ];
    }
}
