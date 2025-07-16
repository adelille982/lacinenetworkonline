<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventEmbeddedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titleEvent', TextType::class)
            ->add('typeEvent', TextType::class)
            ->add('numberEdition', TextType::class)
            ->add('dateEvent', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('textEvent', TextareaType::class)
            ->add('programEvent', TextareaType::class)
            ->add('imgEvent', TextType::class)
            ->add('free', CheckboxType::class, [
                'required' => false,
            ])
            ->add('draft', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
