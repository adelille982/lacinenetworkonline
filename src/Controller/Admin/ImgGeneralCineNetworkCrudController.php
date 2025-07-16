<?php

namespace App\Controller\Admin;

use App\Entity\GeneralCineNetwork;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImgGeneralCineNetworkCrudController extends AbstractCrudController
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    public static function getEntityFqcn(): string
    {
        return GeneralCineNetwork::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Image du formulaire')
            ->setEntityLabelInPlural('Images des formulaires')
            ->setDefaultSort(['id' => 'ASC'])
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        $relativePath = 'images/general/images-des-formulaires/';
        $absolutePath = $this->projectDir . '/public/' . $relativePath;

        $filesystem = new Filesystem();
        $images = [];

        if ($filesystem->exists($absolutePath)) {
            $images = array_diff(scandir($absolutePath), ['.', '..']);
        }

        $imageChoices = array_combine($images, $images);

        return [
            FormField::addTab('image du formulaire "Formation"')->setIcon('fas fa-image'),

            ChoiceField::new('imgFormNetPitch', 'Sélectionner une image formulaire Net Pitch')
                ->setChoices($imageChoices)
                ->onlyOnForms()
                ->setHelp('
                <span style="color:red;">Image affichée sur le formulaire de pré-inscription à une formation.</span><br><hr>
                <details>
                <summary style="color:#FFA500; cursor:pointer;">Conseils pour choisir une image adaptée</summary>
                <div style="margin-top:10px;">
                <ul>
                <li>Image à placer dans le dossier <code>/public/images/général/images-des-formulaires/</code></li>
                <li>Format recommandé : <strong>JPG</strong> ou <strong>WebP</strong>, 16:9</li>
                <li>Visuel clair, évocateur de la formation (ex. : salle, intervenants, réseau)</li>
                </ul>
                </div>
                </details>
                '),

            ImageField::new('imgFormNetPitch', 'Image formulaire Net Pitch')
                ->setBasePath('/' . $relativePath)
                ->setUploadDir('public/' . $relativePath)
                ->onlyOnIndex(),

            FormField::addTab('image du formulaire "Annonce"')->setIcon('fas fa-image'),
            ChoiceField::new('imgFormAnnouncement', 'Sélectionner une image formulaire Annonces')
                ->setChoices($imageChoices)
                ->onlyOnForms()
                ->setHelp('
                <span style="color:red;">Image utilisée dans le formulaire de publication d’une annonce.</span><br><hr>
                <details>
                <summary style="color:#FFA500; cursor:pointer;">Instructions de sélection</summary>
                <div style="margin-top:10px;">
                <ul>
                <li>Déposer les images dans le dossier <code>/public/images/général/images-des-formulaires/</code></li>
                <li>Utiliser des visuels sobres, en lien avec le recrutement ou la recherche de profils</li>
                <li>Préférence pour les formats horizontaux, sans texte incrusté</li>
                </ul>
                </div>
                </details>
                '),

            ImageField::new('imgFormAnnouncement', 'Image formulaire Annonces')
                ->setBasePath('/' . $relativePath)
                ->setUploadDir('public/' . $relativePath)
                ->onlyOnIndex(),

            FormField::addTab('image du formulaire "Postuler"')->setIcon('fas fa-image'),
            ChoiceField::new('imgFormPostulate', 'Sélectionner une image formulaire Postuler')
                ->setChoices($imageChoices)
                ->onlyOnForms()
                ->setHelp('
                <span style="color:red;">Image affichée en haut du formulaire de candidature.</span><br><hr>
                <details>
                <summary style="color:#FFA500; cursor:pointer;">Recommandations visuelles</summary>
                <div style="margin-top:10px;">
                <ul>
                <li>Nom du fichier à sélectionner parmi ceux du dossier <code>/images/général/images-des-formulaires</code></li>
                <li>Image engageante, représentant des personnes ou des projets audiovisuels</li>
                <li>Éviter les images trop chargées ou avec texte</li>
                </ul>
                </div>
                </details>
                '),

            ImageField::new('imgFormPostulate', 'Image formulaire Postuler')
                ->setBasePath('/' . $relativePath)
                ->setUploadDir('public/' . $relativePath)
                ->onlyOnIndex(),

            FormField::addTab('image du formulaire  "Commentaires de la page "La Ciné Network"')->setIcon('fas fa-image'),
            ChoiceField::new('imgCommentNetwork', 'Sélectionner une image formulaire Commentaires Événements')
                ->setChoices($imageChoices)
                ->onlyOnForms()
                ->setHelp('
                <span style="color:red;">Image utilisée dans le formulaire de dépôt de commentaires (événements cinéma).</span><br><hr>
                <details>
                <summary style="color:#FFA500; cursor:pointer;">À propos de cette image</summary>
                <div style="margin-top:10px;">
                <ul>
                <li>Elle doit refléter l’ambiance des événements ou projections</li>
                <li>Dimensions équilibrées (16:9) – privilégier la sobriété</li>
                <li>Stockée dans <code>/public/images/général/images-des-formulaires/</code></li>
                </ul>
                </div>
                </details>
                '),

            ImageField::new('imgCommentNetwork', 'Image formulaire Commentaires Événements')
                ->setBasePath('/' . $relativePath)
                ->setUploadDir('public/' . $relativePath)
                ->onlyOnIndex(),

            FormField::addTab('image du formulaire "Commentaires de la page "Formations"')->setIcon('fas fa-image'),
            ChoiceField::new('imgCommentNetPitch', 'Sélectionner une image formulaire Commentaires Formations')
                ->setChoices($imageChoices)
                ->onlyOnForms()
                ->setHelp('
                <span style="color:red;">Image affichée dans le formulaire de commentaire en bas de la page de formation.</span><br><hr>
                <details>
                <summary style="color:#FFA500; cursor:pointer;">Format & contexte</summary>
                <div style="margin-top:10px;">
                <ul>
                <li>Image à thème pédagogique ou réseau professionnel</li>
                <li>Pas de texte dans l’image, fond épuré conseillé</li>
                <li>Doit être présente dans <code>/images/général/images-des-formulaires/</code></li>
                </ul>
                </div>
                </details>
                '),

            ImageField::new('imgCommentNetPitch', 'Image formulaire Commentaires Formations')
                ->setBasePath('/' . $relativePath)
                ->setUploadDir('public/' . $relativePath)
                ->onlyOnIndex(),

            FormField::addTab('image du formulaire "Connexion"')->setIcon('fas fa-image'),
            ChoiceField::new('imgFormLogin', 'Sélectionner une image formulaire Connexion')
                ->setChoices($imageChoices)
                ->onlyOnForms()
                ->setHelp('
                <span style="color:red;">Image affichée dans le formulaire de connexion.</span><br><hr>
                <details>
                <summary style="color:#FFA500; cursor:pointer;">Format & contexte</summary>
                <div style="margin-top:10px;">
                <ul>
                <li>Image à thème pédagogique ou réseau professionnel</li>
                <li>Pas de texte dans l’image, fond épuré conseillé</li>
                <li>Doit être présente dans <code>/images/général/images-des-formulaires/</code></li>
                </ul>
                </div>
                </details>
                '),

            ImageField::new('imgFormLogin', 'Image formulaire Connexion')
                ->setBasePath('/' . $relativePath)
                ->setUploadDir('public/' . $relativePath)
                ->onlyOnIndex(),

            FormField::addTab('image du formulaire "Inscription"')->setIcon('fas fa-image'),
            ChoiceField::new('imgFormRegistration', 'Sélectionner une image formulaire d\'inscription')
                ->setChoices($imageChoices)
                ->onlyOnForms()
                ->setHelp('
                <span style="color:red;">Image affichée dans le formulaire d\'inscription</span><br><hr>
                <details>
                <summary style="color:#FFA500; cursor:pointer;">Format & contexte</summary>
                <div style="margin-top:10px;">
                <ul>
                <li>Image à thème pédagogique ou réseau professionnel</li>
                <li>Pas de texte dans l’image, fond épuré conseillé</li>
                <li>Doit être présente dans <code>/images/général/images-des-formulaires/</code></li>
                </ul>
                </div>
                </details>
                '),

            ImageField::new('imgFormRegistration', 'Image formulaire d\'inscription')
                ->setBasePath('/' . $relativePath)
                ->setUploadDir('public/' . $relativePath)
                ->onlyOnIndex(),

            FormField::addTab('image du formulaire "Mot de passe oublié"')->setIcon('fas fa-image'),
            ChoiceField::new('imgFormResetPasword', 'Sélectionner une image formulaire d\'inscription')
                ->setChoices($imageChoices)
                ->onlyOnForms()
                ->setHelp('
                <span style="color:red;">Image affichée dans le formulaire de mot de passe oublié</span><br><hr>
                <details>
                <summary style="color:#FFA500; cursor:pointer;">Format & contexte</summary>
                <div style="margin-top:10px;">
                <ul>
                <li>Image à thème pédagogique ou réseau professionnel</li>
                <li>Pas de texte dans l’image, fond épuré conseillé</li>
                <li>Doit être présente dans <code>/images/général/images-des-formulaires/</code></li>
                </ul>
                </div>
                </details>
                '),

            ImageField::new('imgFormResetPasword', 'Image formulaire Mot de passe oublié')
                ->setBasePath('/' . $relativePath)
                ->setUploadDir('public/' . $relativePath)
                ->onlyOnIndex(),

            FormField::addTab('image par défault')->setIcon('fas fa-image'),
            ChoiceField::new('replacementImage', 'Sélectionner une image par défaut pour les utilisateurs')
                ->setChoices($imageChoices)
                ->onlyOnForms()
                ->setHelp('
                <span style="color:red; font-weight:bold;">Image affichée lorsqu’un utilisateur n’a pas encore défini de photo de profil.</span>
                <hr>
                <details>
                <summary style="color:#FFA500; cursor:pointer;"><strong>Formats & recommandations</strong></summary>
                <div style="margin-top:10px;">
                    <ul style="padding-left:18px; margin:5px 0;">
                    <li>Image neutre ou à thème professionnel/pédagogique</li>
                    <li>Sans texte intégré, avec un fond sobre</li>
                    <li>Doit être présente dans : <code>/images/général/images-des-formulaires/</code></li>
                    </ul>
                </div>
                </details>
                '),

            ImageField::new('replacementImage', 'Image par défault')
                ->setBasePath('/' . $relativePath)
                ->setUploadDir('public/' . $relativePath)
                ->onlyOnIndex(),

            FormField::addTab('image "À Propos"')->setIcon('fas fa-image'),
            ChoiceField::new('imgAbout', 'Sélectionner une image pour "À Propos"')
                ->setChoices($imageChoices)
                ->onlyOnForms()
                ->setHelp('
                <span style="color:red; font-weight:bold;">Image affichée sur la page ""À Propos""</span>
                <hr>
                <details>
                <summary style="color:#FFA500; cursor:pointer;"><strong>Formats & recommandations</strong></summary>
                <div style="margin-top:10px;">
                    <ul style="padding-left:18px; margin:5px 0;">
                    <li>Image neutre ou à thème professionnel/pédagogique</li>
                    <li>Sans texte intégré, avec un fond sobre</li>
                    <li>Doit être présente dans : <code>/images/général/images-des-formulaires/</code></li>
                    </ul>
                </div>
                </details>
                '),

            ImageField::new('imgAbout', 'Image "À Propos"')
                ->setBasePath('/' . $relativePath)
                ->setUploadDir('public/' . $relativePath)
                ->onlyOnIndex(),
        ];
    }
}
