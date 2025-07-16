<?php

namespace App\Controller\Admin;

use App\Entity\BlogPost;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BlogPostCrudController extends AbstractCrudController
{
    private string $imageDir;
    private string $videoDir;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(ParameterBagInterface $params, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->imageDir = $params->get('kernel.project_dir') . '/public/images/blog/article-de-blog/';
        $this->videoDir = $params->get('kernel.project_dir') . '/public/images/blog/video-blog/';
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return BlogPost::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(
                TextFilter::new('titlePost', 'Titre')
                    ->setFormTypeOption('attr', ['placeholder' => 'ex : Festival de Cannes…'])
            )
            ->add(
                TextFilter::new('authorBlogPost', 'Auteur')
                    ->setFormTypeOption('attr', ['placeholder' => 'ex : Jean Dupont'])
            )
            ->add(
                DateTimeFilter::new('publicationDateBlogPost', 'Date de publication')
            )
            ->add(
                BooleanFilter::new('draft', 'Brouillon ?')
            )
            ->add(
                EntityFilter::new('blogCategories', 'Catégories liées')
            );
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Article de blog')
            ->setEntityLabelInPlural('Articles de blog')
            ->setDefaultSort(['publicationDateBlogPost' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewPublicAction = Action::new('voirArticle', 'Voir l’article', 'fa fa-eye')
            ->linkToUrl(function (BlogPost $entity) {
                return $this->generateUrl('app_blog_post_show', [
                    'slug' => $entity->getSlugBlogPost(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);
            })
            ->setHtmlAttributes([
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
            ])
            ->addCssClass('btn btn-success');

        return $actions
            ->disable(Action::DELETE)
            ->add(Crud::PAGE_INDEX, $viewPublicAction)
            ->add(Crud::PAGE_DETAIL, $viewPublicAction)
            ->add(Crud::PAGE_EDIT, $viewPublicAction)
            ->add(Crud::PAGE_NEW, $viewPublicAction)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setLabel('Fiche')
                    ->setIcon('fa fa-file-alt')
                    ->addCssClass('btn btn-info');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        $filesystem = new Filesystem();

        $imageChoices = [];
        if ($filesystem->exists($this->imageDir)) {
            $images = array_diff(scandir($this->imageDir), ['.', '..']);
            $imageChoices = array_combine($images, $images);
        }

        $videoChoices = [];
        if ($filesystem->exists($this->videoDir)) {
            $videos = array_diff(scandir($this->videoDir), ['.', '..']);
            $videoChoices = array_combine($videos, $videos);
        }

        return [
            FormField::addTab('Slug')->setIcon('fa fa-link'),
            TextField::new('slugBlogPost', 'Slug')->hideOnIndex()
                ->setHelp('
        <span style="color: red;">
            Le slug est l’identifiant unique de l’URL pour cette catégorie. Il ne doit contenir que des lettres minuscules, des tirets et aucun accent.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Conseils pour bien le rédiger (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Utilisez des mots-clés représentatifs de la catégorie.</li>
                    <li>Remplacez les espaces par des <strong>tirets</strong>.</li>
                    <li><em>Exemple : “actualité-cinema” au lieu de “Actualité Cinéma”.</em></li>
                    <li>Ne modifiez pas un slug utilisé si l’article est déjà en ligne (impact SEO).</li>
                </ul>
                <p><em>Un bon slug améliore la lisibilité des URLs et le référencement naturel.</em></p>
            </div>
        </details>
    '),

            FormField::addTab('Informations principales')->setIcon('fa fa-info-circle'),
            TextField::new('titlePost', 'Titre')
                ->setHelp('<small>Le titre principal visible par les visiteurs du blog.</small>'),
            TextField::new('authorBlogPost', 'Auteur')
                ->setHelp('<small>Nom de l’auteur affiché sous le titre ou en haut de l’article.</small>'),
            DateField::new('publicationDateBlogPost', 'Date de publication')
                ->hideWhenUpdating()
                ->hideWhenCreating(),

            FormField::addTab('Contenu')->setIcon('fa fa-align-left'),
            TexteditorField::new('shortDescriptionBlogPost', 'Introduction')
                ->formatValue(fn($value) => strip_tags($value))
                ->setHelp('
<span style="color: red;">
    Ce champ correspond à l’introduction visible dans les aperçus (page d’accueil, catégories, etc.).
</span>
<br><hr>
<details>
    <summary style="color: #FFA500; cursor: pointer;">Conseils pour une introduction efficace (cliquez ici)</summary>
    <div style="margin-top: 10px;">
        <ul>
            <li>Rédigez sur <strong>Word ou Google Docs</strong> (titres, paragraphes), puis copiez-collez ici.</li>
            <li>Résumez en une ou deux phrases l’essentiel de l’article.</li>
            <li>Utilisez un ton engageant pour donner envie de lire la suite.</li>
            <li>Ajoutez une touche de style ou une question ouverte si pertinent.</li>
            <li><em>Exemple : “Comment le cinéma indépendant séduit-il un nouveau public ?”</em></li>
        </ul>
        <p><em>L’introduction sert d’accroche : elle doit être claire, concise et incitative.</em></p>
    </div>
</details>
'),
            TextEditorField::new('contentBlogPost', 'Contenu principal')
                ->formatValue(fn($value) => strip_tags($value))
                ->setHelp('
<span style="color: red;">
    Rédigez ici le corps principal de l’article visible par les visiteurs.
</span>
<br><hr>
<details>
    <summary style="color: #FFA500; cursor: pointer;">Recommandations de rédaction (cliquez ici)</summary>
    <div style="margin-top: 10px;">
        <ul>
            <li>Rédigez sur <strong>Word ou Google Docs</strong> (titres, paragraphes), puis copiez-collez ici.</li>
            <li>Séparez clairement les idées en paragraphes courts.</li>
        </ul>
        <p><em>Ce bloc doit être informatif, captivant et bien hiérarchisé pour faciliter la lecture.</em></p>
    </div>
</details>
'),
            TextEditorField::new('contentBlogPost2', 'Contenu secondaire')
                ->formatValue(fn($value) => strip_tags($value))
                ->setHelp('
<span style="color: red;">
    Ce champ est optionnel et peut servir à ajouter un encart spécial, des bonus ou un second sujet.
</span>
<br><hr>
<details>
    <summary style="color: #FFA500; cursor: pointer;">Suggestions d’utilisation (cliquez ici)</summary>
    <div style="margin-top: 10px;">
        <ul>
            <li>Rédigez sur <strong>Word ou Google Docs</strong> (titres, paragraphes), puis copiez-collez ici.</li>
            <li>Ajoutez ici des interviews, anecdotes ou citations supplémentaires.</li>
            <li>Utilisez-le pour séparer deux axes ou approfondir un point traité plus haut.</li>
            <li>Peut aussi servir de conclusion enrichie ou section “Pour aller plus loin”.</li>
        </ul>
        <p><em>Ce contenu vient en complément, mais doit garder la même qualité rédactionnelle.</em></p>
    </div>
</details>
'),

            FormField::addTab('Images')->setIcon('fa fa-image'),
            ChoiceField::new('mainImgPost', 'Sélectionner une image principale')
                ->setChoices($imageChoices)
                ->setFormTypeOption('empty_data', '')
                ->setRequired(false)
                ->onlyOnForms()
                ->setHelp('
<span style="color: red;">Image principale de l’article.</span>
<br><hr>
<details>
    <summary style="color: #FFA500; cursor: pointer;">Conseils pour bien la sélectionner (cliquez ici)</summary>
    <div style="margin-top: 10px;">
        <ul>
            <li>Affichée dans les aperçus (blog, accueil) et en haut de l’article.</li>
            <li>Choisissez une image percutante, cohérente avec le sujet traité.</li>
            <li><strong>Format recommandé :</strong> JPG ou WebP, orientation paysage, min. 1200px de large.</li>
            <li><strong>Répertoire :</strong> <code>/images/blog/article-de-blog/</code></li>
            <li>Évitez les noms de fichiers avec accents ou espaces.</li>
        </ul>
        <p><em>Cette image est votre accroche visuelle principale : impact et qualité sont essentiels.</em></p>
    </div>
</details>
'),

            ImageField::new('mainImgPost', 'Image principale')
                ->setBasePath('images/blog/article-de-blog/')
                ->setUploadDir('public/images/blog/article-de-blog/')
                ->onlyOnIndex(),

            ImageField::new('mainImgPost', 'Image principale')
                ->setBasePath('images/blog/article-de-blog/')
                ->onlyOnDetail(),

            ChoiceField::new('imgNumber2', 'Sélectionner une image secondaire')
                ->setChoices($imageChoices)
                ->setFormTypeOption('empty_data', '')
                ->setRequired(false)
                ->onlyOnForms()
                ->setHelp('
<span style="color: red;">Illustration optionnelle d’un passage clé du contenu.</span>
<br><hr>
<details>
    <summary style="color: #FFA500; cursor: pointer;">À quoi sert cette image ? (cliquez ici)</summary>
    <div style="margin-top: 10px;">
        <ul>
            <li>Peut illustrer une citation, une anecdote ou un témoignage dans l’article.</li>
            <li><strong>⚠️ Si une vidéo est sélectionnée, cette image ne sera pas affichée.</strong></li>
            <li><strong>Format conseillé :</strong> JPG ou WebP, paysage, bonne lisibilité.</li>
            <li><strong>Répertoire :</strong> <code>/images/blog/article-de-blog/</code></li>
        </ul>
        <p><em>Elle rythme la lecture visuelle. Inutile si une vidéo est déjà mise en avant.</em></p>
    </div>
</details>
'),

            ImageField::new('imgNumber2', 'Image secondaire')
                ->setBasePath('images/blog/article-de-blog/')
                ->setUploadDir('public/images/blog/article-de-blog/')
                ->onlyOnIndex(),

            ImageField::new('imgNumber2', 'Image secondaire')
                ->setBasePath('images/blog/article-de-blog/')
                ->onlyOnDetail(),

            ChoiceField::new('imgNumber3', 'Sélectionner une troisième image')
                ->setChoices($imageChoices)
                ->setFormTypeOption('empty_data', '')
                ->setRequired(false)
                ->onlyOnForms()
                ->setHelp('
<span style="color: red;">Image facultative pour enrichir visuellement l’article.</span>
<br><hr>
<details>
    <summary style="color: #FFA500; cursor: pointer;">Utilisation recommandée (cliquez ici)</summary>
    <div style="margin-top: 10px;">
        <ul>
            <li>À insérer entre deux sections ou pour illustrer un second point fort.</li>
            <li><strong>Format :</strong> JPG ou WebP, paysage, optimisé pour le web.</li>
            <li><strong>Répertoire :</strong> <code>/images/blog/article-de-blog/</code></li>
            <li><em>Facultative, mais utile pour éviter des blocs trop longs sans illustration.</em></li>
        </ul>
    </div>
</details>
'),
            ImageField::new('imgNumber3', 'Troisième image')
                ->setBasePath('images/blog/article-de-blog/')
                ->setUploadDir('public/images/blog/article-de-blog/')
                ->onlyOnIndex(),

            ImageField::new('imgNumber3', 'Troisième image')
                ->setBasePath('images/blog/article-de-blog/')
                ->onlyOnDetail(),

            ChoiceField::new('imgNumber4', 'Sélectionner une quatrième image')
                ->setChoices($imageChoices)
                ->setFormTypeOption('empty_data', '')
                ->setRequired(false)
                ->onlyOnForms()
                ->setHelp('
<span style="color: red;">Dernière image de l’article (facultative).</span>
<br><hr>
<details>
    <summary style="color: #FFA500; cursor: pointer;">Quand et comment l’utiliser ?</summary>
    <div style="margin-top: 10px;">
        <ul>
            <li>Peut servir à conclure visuellement l’article ou à illustrer un message final.</li>
            <li><strong>Format :</strong> JPG ou WebP, paysage, cohérent avec le reste.</li>
            <li><strong>Répertoire :</strong> <code>/images/blog/article-de-blog/</code></li>
            <li>Évitez la surcharge visuelle : 3 à 4 images maximum par article suffisent.</li>
        </ul>
        <p><em>Utilisez-la uniquement si elle ajoute de la valeur ou du sens à la fin de lecture.</em></p>
    </div>
</details>
'),

            ImageField::new('imgNumber4', 'Quatrième image')
                ->setBasePath('images/blog/article-de-blog/')
                ->setUploadDir('public/images/blog/article-de-blog/')
                ->onlyOnIndex(),

            ImageField::new('imgNumber4', 'Quatrième image')
                ->setBasePath('images/blog/article-de-blog/')
                ->onlyOnDetail(),

            FormField::addTab('Vidéo')->setIcon('fas fa-video'),
            ChoiceField::new('videoBlogPost', 'Vidéo')
                ->setChoices($videoChoices)
                ->setHelp('
        <span style="color: red;">
            Sélectionnez une vidéo déjà présente dans le dossier <code>/images/blog/vidéo-blog/</code>.
        </span>
        <br><small>
            Le fichier doit être un <strong>.mp4</strong>. Il sera affiché dans la fiche article sous forme de lecteur.
        </small>
    ')
                ->onlyOnForms(),

            TextField::new('videoBlogPost', 'Vidéo')
                ->onlyOnIndex()
                ->formatValue(function ($value) {
                    if (!$value) return '';
                    return sprintf(
                        '<video width="160" height="90" controls style="object-fit:cover; border-radius:8px;">
                <source src="/images/blog/video-blog/%s" type="video/mp4">
                Votre navigateur ne supporte pas la lecture vidéo.
            </video>',
                        htmlspecialchars($value)
                    );
                })
                ->renderAsHtml(),

            TextField::new('videoBlogPost', 'Vidéo')
                ->onlyOnDetail()
                ->formatValue(function ($value) {
                    if (!$value) return '';
                    return sprintf(
                        '<video width="480" height="270" controls style="object-fit:cover; border-radius:8px;">
                <source src="/images/blog/video-blog/%s" type="video/mp4">
                Votre navigateur ne supporte pas la lecture vidéo.
            </video>',
                        htmlspecialchars($value)
                    );
                })
                ->renderAsHtml(),

            FormField::addTab('SEO')->setIcon('fa fa-search'),
            TextareaField::new('metaDescriptionBlogPost', 'Méta Description')->hideOnIndex()
                ->setHelp('
        <span style="color: red;">
            Ce champ est destiné aux moteurs de recherche. Il résume le contenu de cette article.
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
            TextField::new('seoKeyBlogPost', 'Mots-clés SEO')->hideOnIndex()
                ->setHelp('
        <span style="color: red;">
            Liste de mots-clés séparés par des virgules, utilisés pour renforcer le SEO de cette article.
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

            FormField::addTab('Catégories associées')->setIcon('fa fa-tags')
                ->onlyOnDetail(),

            AssociationField::new('blogCategories', 'Catégories liées')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->formatValue(function ($value) {
                    if ($value instanceof \Doctrine\Common\Collections\Collection && !$value->isEmpty()) {
                        return implode('<br>', $value->map(function ($category) {
                            $url = $this->adminUrlGenerator
                                ->setController(\App\Controller\Admin\BlogCategoryCrudController::class)
                                ->setAction('detail')
                                ->setEntityId($category->getId())
                                ->generateUrl();

                            return sprintf('<a href="%s">%s</a>', $url, htmlspecialchars($category->getNameBlogCategory()));
                        })->toArray());
                    }

                    return '<i>Aucune catégorie liée</i>';
                })
                ->renderAsHtml()
                ->setHelp('Affiche les noms des catégories liées à cet article, avec accès direct aux fiches.'),

            FormField::addTab('Articles associés')->setIcon('fa fa-tags'),

            AssociationField::new('blogPosts', 'Articles liés')
                ->formatValue(function ($value) {
                    if ($value instanceof \Doctrine\Common\Collections\Collection && !$value->isEmpty()) {
                        return implode('<br>', $value->map(function ($post) {
                            $url = $this->adminUrlGenerator
                                ->setController(\App\Controller\Admin\BlogPostCrudController::class)
                                ->setAction('detail')
                                ->setEntityId($post->getId())
                                ->generateUrl();

                            return sprintf('<a href="%s">%s</a>', $url, htmlspecialchars($post->getTitlePost()));
                        })->toArray());
                    }

                    return '<i>Aucun article lié</i>';
                })
                ->renderAsHtml()
                ->setHelp('
<span style="color: red;">
    Le maillage interne est un élément clé du référencement naturel (SEO) et de l’expérience utilisateur.
</span>
<br><hr>
<details>
    <summary style="color: #FFA500; cursor: pointer;">Pourquoi associer des articles entre eux ?</summary>
    <div style="margin-top: 10px;">
        <ul>
            <li><strong>Renforce le SEO :</strong> les liens internes permettent aux moteurs de recherche de mieux explorer et comprendre la structure de ton site.</li>
            <li><strong>Augmente le temps passé sur le site :</strong> les visiteurs peuvent rebondir vers d’autres articles complémentaires.</li>
            <li><strong>Crée un parcours de lecture logique :</strong> lie des articles sur des thématiques proches ou des suites de contenus.</li>
        </ul>
        <hr>
        <p><strong>Exemples d’application :</strong></p>
        <ul>
            <li>Un article sur l’écriture de scénario peut être lié à un article sur la structure narrative.</li>
            <li>Une actualité de festival peut renvoyer vers une fiche réalisateur ou un court-métrage projeté.</li>
        </ul>
        <p><em>Ajoute au moins 1 article pour renforcer l’impact. Le lien esy visible dans la fiche, et le titre titre est cliquable.</em></p>
    </div>
</details>
'),
            FormField::addTab('Brouillon')->setIcon('fas fa-pencil-alt'),
            BooleanField::new('draft', 'Brouillon')
                ->setHelp('<small>Si activé, l’article n’apparaîtra pas sur le site public.</small>'),
        ];
    }
}
