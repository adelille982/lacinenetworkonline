<?php

namespace App\Controller\Admin;

use App\Entity\BlogCategory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;

class BlogCategoryCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return BlogCategory::class;
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

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(
                TextFilter::new('nameBlogCategory', 'Nom de la catégorie')
                    ->setFormTypeOption('attr', ['placeholder' => 'ex : Interviews, Tournage...'])
            )
            ->add(
                TextFilter::new('slugBlogCategory', 'Slug (URL)')
                    ->setFormTypeOption('attr', ['placeholder' => 'ex : actualites-cinema'])
            )
            ->add(
                EntityFilter::new('blogPost', 'Articles associés')
            );
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Catégorie de blog')
            ->setEntityLabelInPlural('Catégories de blog')
            ->setDefaultSort(['nameBlogCategory' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Slug')->setIcon('fa fa-link'),
            TextField::new('slugBlogCategory', 'Slug (URL)')
                ->hideOnIndex()
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

            FormField::addTab('Informations générales')->setIcon('fa fa-info-circle'),
            TextField::new('nameBlogCategory', 'Nom de la catégorie')
                ->setHelp('
        <span style="color: red;">
            C’est le nom affiché publiquement pour cette catégorie dans les menus, filtres et pages.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Bonnes pratiques de nommage (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Privilégiez les noms courts, clairs et explicites.</li>
                    <li>Évitez les abréviations ou les formulations trop longues.</li>
                    <li><em>Exemple : “Interviews”, “Conseils pros”, “Coulisses du tournage”</em></li>
                </ul>
                <p><em>Ce nom aide les visiteurs à se repérer rapidement dans les contenus proposés.</em></p>
            </div>
        </details>
    '),

            FormField::addTab('SEO')->setIcon('fa fa-search'),
            TextareaField::new('metaDescriptionBlogCategory', 'Meta Description')
                ->hideOnIndex()
                ->setHelp('
        <span style="color: red;">
            Ce champ est destiné aux moteurs de recherche. Il résume le contenu de cette catégorie.
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
            TextField::new('seoKeyBlogCategory', 'Mots-clés SEO')
                ->hideOnIndex()
                ->setHelp('
        <span style="color: red;">
            Liste de mots-clés séparés par des virgules, utilisés pour renforcer le SEO de cette catégorie.
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

            FormField::addTab('Articles associés')->setIcon('fa fa-file-alt')
                ->onlyOnDetail(),
            AssociationField::new('blogPost', 'Articles associés')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->formatValue(function ($value) {
                    if ($value instanceof \Doctrine\Common\Collections\Collection && !$value->isEmpty()) {
                        return implode('<br>', $value->map(function ($post) {
                            $url = $this->adminUrlGenerator
                                ->setController(BlogPostCrudController::class)
                                ->setAction('detail')
                                ->setEntityId($post->getId())
                                ->generateUrl();

                            return sprintf('<a href="%s">%s</a>', $url, htmlspecialchars($post->getTitlePost()));
                        })->toArray());
                    }

                    return '<i>Aucun article lié</i>';
                })
                ->renderAsHtml()
                ->setHelp('Affiche les titres des articles liés à cette catégorie de blog.'),
        ];
    }
}
