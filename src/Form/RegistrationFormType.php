<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstnameUser', TextType::class, [
                'label' => 'Prénom *',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre prénom']),
                ],
            ])
            ->add('lastnameUser', TextType::class, [
                'label' => 'Nom *',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nom']),
                ],
            ])
            ->add('email', null, [
                'label' => 'Email *',
                'attr' => [
                    'placeholder' => 'Veuillez entrer votre email'
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe *',
                'mapped' => false,
                'attr' => ['autocomplete' => 'nouveau-mot-de-passe'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe']),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                    new Regex([
                        'pattern' => '/(?=.*[A-Z])(?=.*[a-z])(?=.*\d).+/',
                        'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.',
                    ]),
                ],
            ])
            ->add('telephoneUser', TelType::class, [
                'label' => 'Numéro de téléphone',
                'required' => false,
            ])
            ->add('pictureUser', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG ou PNG)',
                    ])
                ],
                'attr' => ['accept' => 'image/png, image/jpeg']
            ])
            ->add('fieldOfEvolutionUser', TextareaType::class, [
                'label' => 'Dans quels domaines artistiques évoluez-vous ?',
                'required' => false,
            ])
            ->add('intermittentUser', ChoiceType::class, [
                'label' => 'Êtes-vous éligible à l\'AFDAS en tant qu\'intermittent ou artiste-auteur ?',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => false, // false = select / true = radio
                'multiple' => false,
                'required' => false,
                'placeholder' => 'Sélectionner une option',
            ])
            ->add('curriculumUser', FileType::class, [
                'label' => 'Téléverser votre CV (PDF uniquement)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['application/pdf'],
                        'mimeTypesMessage' => 'Veuillez téléverser un fichier PDF valide.',
                    ]),
                ],
                'attr' => ['accept' => 'application/pdf']
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J’accepte les conditions générales d’utilisation *',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions générales d’utilisation.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
