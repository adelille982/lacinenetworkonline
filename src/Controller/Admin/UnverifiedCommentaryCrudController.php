<?php

namespace App\Controller\Admin;

use App\Entity\Commentary;
use App\Repository\GeneralCineNetworkRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class UnverifiedCommentaryCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;
    private ManagerRegistry $doctrine;
    private MailerInterface $mailer;

    public function __construct(AdminUrlGenerator $adminUrlGenerator, ManagerRegistry $doctrine, MailerInterface $mailer)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->doctrine = $doctrine;
        $this->mailer = $mailer;
    }

    public static function getEntityFqcn(): string
    {
        return Commentary::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $validateAction = Action::new('validate', 'Valider', 'fas fa-check')
            ->linkToUrl(function (Commentary $commentary) {
                return $this->adminUrlGenerator
                    ->setRoute('admin_validate_comment', ['id' => $commentary->getId()])
                    ->generateUrl();
            })
            ->setCssClass('btn btn-success');

        $rejectAction = Action::new('reject', 'Rejeter', 'fas fa-times')
            ->linkToUrl(function (Commentary $commentary) {
                return $this->adminUrlGenerator
                    ->setRoute('admin_reject_comment', ['id' => $commentary->getId()])
                    ->generateUrl();
            })
            ->setHtmlAttributes([
                'onclick' => "return confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce commentaire ? Cette action est irréversible.')",
            ])
            ->setCssClass('btn btn-danger');

        return $actions
            ->add(Crud::PAGE_INDEX, $validateAction)
            ->add(Crud::PAGE_INDEX, $rejectAction)
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setLabel('Fiche')
                    ->setIcon('fas fa-book-open')
                    ->addCssClass('btn btn-info');
            });
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Commentaires à vérifier')
            ->setEntityLabelInSingular('Commentaire à vérifier')
            ->setDefaultSort(['validatedAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(
                EntityFilter::new('user', 'Auteur du commentaire')
            )
            ->add(
                TextFilter::new('textCommentary', 'Contenu du commentaire')
                    ->setFormTypeOption('attr', ['placeholder' => 'Recherche dans le texte...'])
            )
            ->add(
                EntityFilter::new('netPitchFormation', 'Formation associée')
            )
            ->add(
                EntityFilter::new('archivedEvent', 'Événement archivé')
            )
            ->add(
                ChoiceFilter::new('statutCommentary', 'Statut du commentaire')
                    ->setChoices([
                        'Validé' => 'validé',
                        'En attente' => 'en attente',
                    ])
            )
            ->add(
                DateTimeFilter::new('createdAt', 'Date de création')
            );
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $qb->andWhere('entity.statutCommentary = :status')
            ->setParameter('status', 'En attente');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Auteur du commentaire')->setIcon(''),
            AssociationField::new('user', 'Auteur')->hideWhenUpdating(),

            FormField::addTab('Texte du commentaire')->setIcon(''),
            TextEditorField::new('textCommentary', 'Commentaire')->hideWhenUpdating(),

            FormField::addTab('Événement ou formation associé au commentaire')->setIcon(''),
            AssociationField::new('archivedEvent', 'Événement (archivé)')
                ->hideWhenUpdating(),
            AssociationField::new('netPitchFormation', 'Formation')
                ->hideWhenUpdating(),

            ChoiceField::new('statutCommentary', 'Statut')
                ->setChoices([
                    'En attente' => 'En attente',
                ])
                ->hideWhenUpdating()
                ->hideWhenCreating(),

            DateTimeField::new('createdAt', 'Créé le')->hideWhenUpdating()
                ->hideWhenCreating(),
        ];
    }

    #[Route('/admin/validate-comment/{id}', name: 'admin_validate_comment')]
    public function validateComment(int $id, GeneralCineNetworkRepository $generalCinenetworkRepository): RedirectResponse
    {
        $em = $this->doctrine->getManager();
        $comment = $em->getRepository(Commentary::class)->find($id);

        if (!$comment) {
            $this->addFlash('danger', 'Commentaire introuvable.');
        } else {
            $comment->setStatutCommentary('Validé');
            $comment->setValidatedAt(new \DateTime());
            $em->flush();

            $user = $comment->getUser();
            if ($user && $user->getEmail()) {
                $html = sprintf(
                    '
                <html><body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px;">
                <table style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <tr><td style="background-color: #000000; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
                        <h1 style="color: #feda00; margin: 0;">La Ciné Network</h1>
                        <p style="color: #ffffff; font-size: 14px;">Commentaire publié</p>
                    </td></tr>
                    <tr><td style="padding: 30px;">
                        <p style="font-size: 16px; color: #333;">Bonjour %s,</p>
                        <p style="font-size: 15px; color: #333; line-height: 1.5;">
                            Merci pour votre contribution ! 🎬<br>
                            Votre commentaire vient d’être validé et est désormais visible sur notre plateforme.
                        </p>
                        <p style="font-size: 14px; color: #666; font-style: italic;">
                            L’équipe La Ciné Network vous remercie pour votre participation à la communauté.
                        </p>
                    </td></tr>
                    <tr><td style="text-align: center; padding: 20px; background-color: #f9f9f9; color: #999; font-size: 12px; border-radius: 0 0 8px 8px;">
                        © %d La Ciné Network — Ne répondez pas à cet email.
                    </td></tr>
                </table></body></html>',
                    $user->getFirstnameUser(),
                    date('Y')
                );

                $cineNetwork = $generalCinenetworkRepository->findOneBy([]);
                $emailFrom = $cineNetwork ? $cineNetwork->getEmailCompany() : 'noreply@fallback.com';

                $email = (new Email())
                    ->from(new Address($emailFrom))
                    ->to($user->getEmail())
                    ->subject('🎉 Votre commentaire a été publié !')
                    ->html($html);

                $this->mailer->send($email);
            }

            $this->addFlash('success', 'Commentaire validé avec succès.');
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }

    #[Route('/admin/reject-comment/{id}', name: 'admin_reject_comment')]
    public function rejectComment(int $id, GeneralCineNetworkRepository $generalCinenetworkRepository): RedirectResponse
    {
        $em = $this->doctrine->getManager();
        $comment = $em->getRepository(Commentary::class)->find($id);

        if (!$comment) {
            $this->addFlash('danger', 'Commentaire introuvable.');
        } else {
            $user = $comment->getUser();
            if ($user && $user->getEmail()) {
                $html = sprintf(
                    '
                <html><body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px;">
                <table style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <tr><td style="background-color: #000000; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
                        <h1 style="color: #feda00; margin: 0;">La Ciné Network</h1>
                        <p style="color: #ffffff; font-size: 14px;">Commentaire refusé</p>
                    </td></tr>
                    <tr><td style="padding: 30px;">
                        <p style="font-size: 16px; color: #333;">Bonjour %s,</p>
                        <p style="font-size: 15px; color: #333; line-height: 1.5;">
                            Nous vous informons que votre commentaire n’a pas été validé par notre équipe de modération.<br><br>
                            Si vous le souhaitez, vous pouvez le reformuler et le soumettre à nouveau.
                        </p>
                        <p style="font-size: 14px; color: #666; font-style: italic;">
                            Merci de votre compréhension.
                        </p>
                    </td></tr>
                    <tr><td style="text-align: center; padding: 20px; background-color: #f9f9f9; color: #999; font-size: 12px; border-radius: 0 0 8px 8px;">
                        © %d La Ciné Network — Ne répondez pas à cet email.
                    </td></tr>
                </table></body></html>',
                    $user->getFirstnameUser(),
                    date('Y')
                );

                $cineNetwork = $generalCinenetworkRepository->findOneBy([]);
                $emailFrom = $cineNetwork ? $cineNetwork->getEmailCompany() : 'noreply@fallback.com';

                $email = (new Email())
                    ->from(new Address($emailFrom))
                    ->to($user->getEmail())
                    ->subject('❌ Votre commentaire a été refusé')
                    ->html($html);

                $this->mailer->send($email);
            }

            $em->remove($comment);
            $em->flush();
            $this->addFlash('warning', 'Commentaire rejeté et supprimé.');
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }
}
