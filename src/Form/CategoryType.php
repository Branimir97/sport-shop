<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name_hr', TextType::class, [
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'form.category_name_placeholder_hr'
                ],
                'label' => 'form.category_name_label_hr',
                'translation_domain' => 'category'
            ])
            ->add('name_en', TextType::class, [
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'form.category_name_placeholder_en'
                ],
                'label' => 'form.category_name_label_en',
                'translation_domain' => 'category'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
