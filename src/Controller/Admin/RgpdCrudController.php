<?php

namespace App\Controller\Admin;

use App\Entity\GeneralCineNetwork;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class RgpdCrudController extends AbstractCrudController
{
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
            ->setEntityLabelInSingular('Texte RGPD')
            ->setEntityLabelInPlural('Texte RGPD')
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextEditorField::new('rgpdContent', 'Texte RGPD')
                ->setHelp(
                    '<span style="color: red;">
            Ce texte correspond à votre politique de confidentialité (obligatoire). Il doit expliquer comment les données des utilisateurs sont collectées, utilisées, protégées et stockées sur le site.
        </span>
        <br><hr>
        <details>
            <summary style="color: #FFA500; cursor: pointer;">Conseils pour bien rédiger votre politique RGPD (cliquez ici)</summary>
            <div style="margin-top: 10px;">
                <ul>
                    <li><strong>Préparez le contenu</strong> dans un document Word ou Google Docs pour bénéficier d’une mise en forme claire avec des titres (H1, H2, etc.) et des paragraphes.</li>
                    <li>Structurez votre texte en sections claires, par exemple :
                        <ul>
                            <li><strong>Qui sommes-nous ?</strong></li>
                            <li><strong>Quelles données collectons-nous ?</strong></li>
                            <li><strong>Comment ces données sont-elles utilisées ?</strong></li>
                            <li><strong>Quels sont vos droits ?</strong> (accès, rectification, suppression...)</li>
                            <li><strong>Contact en cas de réclamation</strong></li>
                        </ul>
                    </li>
                    <li>Évitez les textes juridiques complexes si vous vous adressez à un grand public. Restez clair et transparent.</li>
                    <li>Une fois rédigé et relu, <strong>copiez-collez le contenu dans ce champ</strong>.</li>
                </ul>
                <p><em>⚠️ Ce texte est important pour la conformité légale de votre site et la confiance des utilisateurs. Prenez le temps de le soigner.</em></p>
            </div>
        </details>'
                ),
        ];
    }
}
