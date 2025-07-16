<?php

namespace App\Controller\Admin;

use App\Entity\GeneralCineNetwork;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class GeneralCineNetworkCrudController extends AbstractCrudController
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
            ->setEntityLabelInSingular('Information générale')
            ->setEntityLabelInPlural('Informations générales')
            ->setDefaultSort(['id' => 'ASC'])
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Coordonnées')->setIcon('fas fa-map-marker-alt'),
            TextField::new('adresseCompany', 'Adresse de l\'entreprise')
                ->setHelp(
                    '<span style="color: red;">
            Indiquez une adresse complète au format recommandé : <strong>numéro + rue + ville + code postal</strong>.
            <br>Exemple : <em>21 Avenue du Général de Gaulle, 75000 Paris</em>
        </span>
        <br>
        <small>
            Cette information peut être utilisée pour la page "contact".
        </small>'
                ),

            TextField::new('telCompany', 'Téléphone')
                ->setHelp(
                    '<span style="color: red;">
            Format attendu : <strong>06 12 34 56 78</strong> ou <strong>0612345678</strong>.
        </span>
        <br>
        <small>
            Ce numéro peut être affiché sur le site pour permettre aux visiteurs de vous contacter facilement.
        </small>'
                ),
                
            TextField::new('emailCompany', 'Email du site')
                ->setHelp(
                    '<span style="color: red;">
            Entrez une adresse email pour le site <strong>valide et fonctionnelle</strong> (ex. : contact@monentreprise.fr).
        </span>
        <br>
        <small>
            Cette adresse sera utilisée pour l’envoi automatique des formulaires de contact, d’inscription ou de confirmation.
        </small>'
                ),

            TextField::new('personalEmail', 'Email personnel')
                ->setHelp(
                    '<span style="color: red;">
            Entrez une adresse email personnel <strong>valide et fonctionnelle</strong> (ex. : mon-nom@gmail.com).
        </span>
        <br>
        <small>
            Cette adresse sera utilisée pour l’envoi automatique des formulaires de contact, d’inscription ou de confirmation.
        </small>'
                ),

            FormField::addTab('Réseaux sociaux')->setIcon('fas fa-share-alt'),
            TextField::new('linkFacebook', 'Lien Facebook')
                ->formatValue(function ($value, $entity) {
                    if ($value) {
                        return sprintf('<a href="%s" target="_blank" class="btn btn-sm btn-primary">Lien Facebook</a>', $value);
                    }
                    return '<span class="text-muted">Aucun lien</span>';
                })
                ->renderAsHtml()
                ->setHelp(
                    '<span style="color: red;">
            Veuillez toujours entrer un lien complet commençant par <strong>https://</strong> ou <strong>http://</strong>.
        </span>
        <br>
        <small>
            Exemple : <code>https://www.facebook.com/LaCineNetwork</code><br>
            Cela garantit que le lien est valide et fonctionne correctement sur le site.
        </small>'
                ),

            TextField::new('linkInstagram', 'Lien Instagram')
                ->formatValue(function ($value, $entity) {
                    if ($value) {
                        return sprintf('<a href="%s" target="_blank" class="btn btn-sm btn-primary">Lien Instagram</a>', $value);
                    }
                    return '<span class="text-muted">Aucun lien</span>';
                })
                ->renderAsHtml()
                ->setHelp(
                    '<span style="color: red;">
            Veuillez toujours entrer un lien complet commençant par <strong>https://</strong> ou <strong>http://</strong>.
        </span>
        <br>
        <small>
            Exemple : <code>https://www.instagram.com/lacinenetwork</code><br>
            Cela garantit que le lien est valide et fonctionne correctement sur le site.
        </small>'
                ),

            FormField::addTab('À propos')->setIcon('fas fa-user-edit'),
            TextEditorField::new('textAbout', 'Texte "À propos"')
                ->setHelp(
                    '<span style="color: red;">
            Ce texte apparaîtra sur la page de présentation du site. Il doit décrire votre projet, vos valeurs, vos objectifs, ou encore votre parcours dans le domaine du cinéma ou de l’audiovisuel.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Conseils pratiques pour rédiger ce texte (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li>Rédigez d’abord votre texte dans <strong>Word, Google Docs ou un autre traitement de texte</strong> pour bénéficier d’une meilleure mise en forme.</li>
                    <li>Utilisez une structure claire avec des <strong>titres hiérarchisés</strong> (H1, H2, H3) et des <strong>paragraphes courts</strong>.</li>
                    <li>Une fois satisfait du rendu, <strong>copiez-collez le contenu ici</strong> dans cet éditeur.</li>
                    <li>Évitez les textes trop longs : soyez synthétique mais pertinent.</li>
                </ul>
                <p><em>Ce texte donnera la première impression aux visiteurs. Il doit être clair, accueillant et donner envie d’en savoir plus sur vous ou votre structure.</em></p>
            </div>
        </details>'
                ),


            FormField::addTab('SEO')->setIcon('fas fa-search'),
            TextEditorField::new('titleGeneralCineNetwork', 'Titre (SEO)')
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

            TextEditorField::new('metaDescriptionGeneralCineNetwork', 'Meta Description')
                ->setHelp(
                    '<span style="color: red;">
            Ce texte résume votre site dans les résultats Google.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Comment bien l’écrire ? (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                Utilisez 1 ou 2 phrases simples (150 à 160 caractères maximum) qui expliquent ce que les visiteurs trouveront sur le site.
                <br>Exemple : <em>Découvrez La Ciné Network, un réseau dédié aux professionnels du cinéma, avec événements, formations et networking.</em>
            </div>
        </details>'
                ),

            TextEditorField::new('seoKeyGeneralCineNetwork', 'Mots-clés SEO')
                ->setHelp(
                    '<span style="color: red;">
            Ces mots aident Google à savoir de quoi parle votre site.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Comment choisir les bons mots ? (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                Séparez les mots par une virgule. Pensez à des mots simples que les gens taperaient sur Google pour vous trouver.
                <br>Exemples : <em>cinéma, courts-métrages, festival, networking, réalisateurs</em>
            </div>
        </details>'
                ),

            FormField::addTab('Proposition de courts métrage')->setIcon('fas fa-file-alt'),
            BooleanField::new('shortFilmProposalHome', 'Proposer un court-métrage depuis le site')
                ->setHelp('<small>Activez cette option pour permettre aux visiteurs d\'accéder au formulaire de proposition de court-métrage depuis le menu principal et le pied de page du site.</small>'),
        ];
    }
}
