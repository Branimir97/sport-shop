<?php

namespace App\Form;

use App\Entity\Size;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SizeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', null, [
                'attr'=>[
                    'placeholder' => 'form.value_placeholder'
                ],
                'label' => 'form.value_label',
                'translation_domain' => 'size'
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'form.type_choices.footwear' => "Obuća",
                    'form.type_choices.clothes' => "Odjeća",
                    'form.type_choices.sport' => "Sport"
                ],
                'help' => 'form.type_help',
                'label' => 'form.type_label',
                'translation_domain' => 'size'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Size::class,
        ]);
    }
}
