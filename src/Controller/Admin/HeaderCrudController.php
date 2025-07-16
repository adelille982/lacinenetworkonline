<?php

namespace App\Controller\Admin;

use App\Entity\Header;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

class HeaderCrudController extends AbstractCrudController
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    public static function getEntityFqcn(): string
    {
        return Header::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $imageDirectory = $this->projectDir . '/public/images/en-tete-de-page/autre/';
        $filesystem = new Filesystem();
        $images = [];

        if ($filesystem->exists($imageDirectory)) {
            $images = array_diff(scandir($imageDirectory), ['.', '..']);
        }

        return [
            IdField::new('id', 'N°')->onlyOnIndex(),

            FormField::addTab('Page concernée')->setIcon('fas fa-file-alt'),
            ChoiceField::new('pageTypeHeader', 'Page concernée')
                ->setChoices([
                    'Formation' => 'Formation',
                    'Annonce' => 'Annonce',
                    'À-propos' => 'À-propos',
                    'Blog' => 'Blog',
                    'Postuler' => 'Postuler',
                    'RGPD' => 'RGPD',
                ])
                ->setHelp(
                    '<span style="color: red;">
                Sélectionnez ici la page du site à laquelle ce bandeau s’appliquera.
            </span>
            <br>
            <small>
                Exemple : si vous choisissez <strong>Formation</strong>, ce visuel s’affichera en haut de la page /formations.
            </small>'
                ),

            FormField::addTab('Image de l\'en tête de page')->setIcon('fas fa-image'),
            ChoiceField::new('mainImageHeader', 'sélectionner une image d\'en tête')
                ->setChoices(array_combine($images, $images))
                ->setRequired(false)
                ->setFormTypeOption('empty_data', '')
                ->onlyOnForms()
                ->setHelp(
                    '<span style="color: red;">
                Choisissez une image qui sera affichée en grand, tout en haut de la page concernée.
            </span>
            <br>
            <small>
                L’image doit être placée dans <code>/public/images/en-tete-de-page/autre</code><br>
                Format recommandé : horizontal (16:9) – JPG, PNG ou WebP.
            </small>'
                ),
            ImageField::new('mainImageHeader', 'Image d\'en tête de page')
                ->setBasePath('/images/en-tete-de-page/autre/')
                ->onlyOnIndex()->setHelp(
                    '<small>
                Aperçu de l’image sélectionnée. Affichée en grand bandeau en haut de la page correspondante.
            </small>'
                ),

            FormField::addTab('Titre & slogan')->setIcon('fas fa-heading'),
            TextEditorField::new('titleHeader', 'Titre')
                ->setHelp(
                    '<span style="color: red;">
                Titre principal affiché par-dessus l’image d’en-tête.
            </span>
            <br>
            <small>
                Restez court et impactant. Ce titre doit capturer l’attention immédiatement.
                <br>Exemple : <em>Rejoignez la communauté des créateurs de cinéma</em>
            </small>'
                ),
            TextEditorField::new('sloganHeader', 'Slogan')
                ->setHelp(
                    '<span style="color: red;">
                Slogan ou message complémentaire affiché sous le titre principal.
            </span>
            <br>
            <small>
                Il complète le titre et peut préciser la mission ou la promesse de la page.
                <br>Exemple : <em>Participez à des événements qui font la différence</em>
            </small>'
                ),

            FormField::addTab('SEO')->setIcon('fas fa-search'),
            TextEditorField::new('titleSeoPage', 'Titre (SEO)')
                ->setHelp(
                    '<span style="color: red;">
            Ce titre apparaîtra dans l\'onglet du navigateur et dans les résultats de recherche Google.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Pourquoi c’est important ? (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                Le titre aide Google à comprendre de quoi parle votre site. 
                Il doit être court (moins de 60 caractères), clair et contenir les mots clés importants.
                <br>Exemple : <em>La Ciné Network – Événements cinéma et networking</em>
            </div>
        </details>'
                ),

            TextEditorField::new('metaDescriptionPage', 'Meta Description')
                ->setHelp('
        <span style="color: red;">
            Ce champ est destiné aux moteurs de recherche.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Recommandations SEO pour la méta description</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Faites une phrase d’environ 150 caractères.</li>
                    <li>Incluez les mots-clés principaux de la catégorie.</li>
                    <li>Évitez le copier-coller du titre ou du contenu.</li>
                    <li>Éveillez la curiosité de l’utilisateur pour l’inciter à cliquer.</li>
                </ul>
                <p><em>La méta description peut apparaître dans les résultats Google. Elle influence le taux de clic.</em></p>
            </div>
        </details>
    '),

            TextEditorField::new('seoKeyPage', 'Mots-clés SEO')
                ->setHelp('
        <span style="color: red;">
            Liste de mots-clés séparés par des virgules, utilisés pour renforcer le SEO.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Comment choisir les bons mots-clés</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Utilisez des expressions réellement tapées par les internautes.</li>
                    <li>Variez les formulations : singulier/pluriel, synonymes, etc.</li>
                    <li><em>Exemple : “réalisation film, production court métrage, tournage cinéma indépendant”</em></li>
                    <li>Ne surchargez pas le champ, 5 à 7 mots-clés pertinents suffisent.</li>
                </ul>
                <p><em>Ces mots-clés sont utilisés en background pour améliorer la visibilité Google.</em></p>
            </div>
        </details>
    '),

            FormField::addTab('Brouillon')->setIcon('fas fa-pencil-alt'),
            BooleanField::new('draft', 'Brouillon')
                ->setHelp(
                    '<small>
                Activez cette case si vous ne souhaitez pas encore afficher ce bandeau sur le site.
                <br>Il sera sauvegardé mais <strong>non visible publiquement</strong>.
            </small>'
                ),
        ];
    }
}
