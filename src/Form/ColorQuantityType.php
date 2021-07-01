<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColorQuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $colors = $options['colors'];
        $counter = 0;
        foreach($colors as $color) {
            $counter++;
            $builder
                ->add('color_'.$counter, null, [
                    'attr' => [
                        'placeholder' => 'form_set_color_quantity.color_placeholder'
                    ],
                    'label' => 'form_set_color_quantity.color_label',
                    'label_translation_parameters' => [
                        '%color_name%' => $color->getName()
                    ],
                    'translation_domain' => 'item'
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'colors' => null
        ]);
    }
}
