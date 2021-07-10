<?php

namespace App\Form;

use App\Entity\Color;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemColorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', ColorType::class, [
                'label' => 'form.value_label',
                'translation_domain' => 'color'
            ])
            ->add('name_hr', TextType::class, [
                'mapped' => false,
                'label' => 'form.name_label_hr',
                'attr' => [
                    'placeholder' => 'form.name_placeholder_hr'
                ],
                'translation_domain' => 'color'
            ])
            ->add('name_en', TextType::class, [
                'mapped' => false,
                'label' => 'form.name_label_en',
                'attr' => [
                    'placeholder' => 'form.name_placeholder_en'
                ],
                'translation_domain' => 'color'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Color::class,
        ]);
    }
}
