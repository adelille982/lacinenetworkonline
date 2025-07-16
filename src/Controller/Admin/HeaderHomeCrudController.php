<?php

namespace App\Controller\Admin;

use App\Entity\HeaderHome;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class HeaderHomeCrudController extends AbstractCrudController
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    public static function getEntityFqcn(): string
    {
        return HeaderHome::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('En tête de la page d\'accueil')
            ->setEntityLabelInPlural('En tête des pages d\'accueils')
            ->setDefaultSort(['id' => 'ASC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        $videoDir = $this->projectDir . '/public/images/en-tete-de-page/accueil/video-accueil/';
        $imageDir = $this->projectDir . '/public/images/en-tete-de-page/accueil/';

        $filesystem = new Filesystem();

        $videoChoices = [];
        if ($filesystem->exists($videoDir)) {
            $videoFiles = array_filter(scandir($videoDir), fn($f) => str_ends_with($f, '.mp4'));
            $videoChoices = array_combine($videoFiles, $videoFiles);
        }

        $imageChoices = [];
        if ($filesystem->exists($imageDir)) {
            $imageFiles = array_filter(scandir($imageDir), fn($f) => preg_match('/\.(jpg|jpeg|png|webp)$/i', $f));
            $imageChoices = array_combine($imageFiles, $imageFiles);
        }

        return [
            IdField::new('id', 'N°')->onlyOnIndex(),

            FormField::addTab('Titre & Slogan')->setIcon('fas fa-heading'),
            TextField::new('titleHeaderHome', 'Titre Principal')
                ->setHelp(
                    '<span style="color: red;">
                Ce texte s’affiche en haut de la page d’accueil, au-dessus de la vidéo.
            </span>
            <br><small>
                Il doit être court, clair et représenter l’essence de votre plateforme (ex. : <em>La Ciné Network</em>).
            </small>'
                ),
            TextField::new('sloganHeaderHome', 'Slogan')
                ->setHelp(
                    '<span style="color: red;">
                Ce slogan est un message court qui s’affiche sous le titre pour accrocher les visiteurs.
            </span>
            <br><small>
                Inspirez confiance, donnez envie ou exprimez votre mission (ex. : <em>Un réseau pour les talents du cinéma</em>).
            </small>'
                ),

            FormField::addTab('Statistiques')->setIcon('fas fa-chart-line'),
            IntegerField::new('numberEdition', 'Éditions')
                ->setHelp(
                    '<small>
                Nombre d’éditions passées. Cela donne du poids à votre événement (preuve d’expérience).
            </small>'
                ),
            IntegerField::new('numberParticipant', 'Participants')
                ->setHelp(
                    '<small>
                Nombre total de participants à vos événements. Important pour démontrer votre impact.
            </small>'
                ),
            IntegerField::new('numberMovieReceived', 'Films reçus')
                ->setHelp(
                    '<small>
                Indiquez combien de films ont été soumis. Cela valorise votre portée.
            </small>'
                ),
            IntegerField::new('numberMovieProjected', 'Films projetés')
                ->setHelp(
                    '<small>
                Indique combien de films ont été réellement projetés. Cela montre votre capacité à concrétiser.
            </small>'
                ),

            FormField::addTab('Images associées')->setIcon('fas fa-images'),
            AssociationField::new('headerHomeImgs', 'Images liées')
                ->setRequired(true)
                ->setFormTypeOptions(['by_reference' => false])
                ->setSortable(false)
                ->setHelp(
                    '<small>
                Ces images seront affichées dans le carrousel visuel de l’en-tête de la page d’accueil. 
                Choisissez des images attractives, représentatives et de bonne qualité.
            </small>'
                ),

            FormField::addTab('Vidéo')->setIcon('fas fa-video'),
            ChoiceField::new('videoHeaderHome', 'Vidéo')
                ->setChoices($videoChoices)
                ->setHelp('Sélectionnez un fichier vidéo .mp4 déjà présent dans /video-accueil')
                ->onlyOnForms()
                ->setHelp(
                    '<span style="color: red;">
                Cette vidéo est affichée dans l’en-tête de la page d’accueil, derrière le titre et le slogan.
            </span>
            <br><small>
                Le fichier doit être un <strong>.mp4</strong> placé dans <code>/public/images/en-tete-de-page/accueil/video-accueil/</code>. 
                Utilisez une vidéo silencieuse, esthétique, qui représente votre univers.
            </small>'
                ),

            TextField::new('videoHeaderHome', 'Vidéo')
                ->onlyOnIndex()
                ->formatValue(function ($value) {
                    if (!$value) return '';
                    return sprintf(
                        '<video width="160" height="90" controls style="object-fit:cover; border-radius:8px;">
                            <source src="/images/en-tete-de-page/accueil/video-accueil/%s" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture vidéo.
                        </video>',
                        htmlspecialchars($value)
                    );
                })
                ->renderAsHtml(),

            FormField::addTab('Brouillon')->setIcon('fas fa-pencil-alt'),
            BooleanField::new('draft', 'Brouillon')
                ->setHelp(
                    '<small>
                Si activé, cette configuration ne sera <strong>pas affichée</strong> publiquement sur la page d’accueil.
                <br>Pratique pour préparer un en-tête en amont.
            </small>'
                ),
        ];
    }
}
