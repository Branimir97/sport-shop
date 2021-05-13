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
                    'label'=>'['.$color->getValue().'] boja - unesite količinu',
                    'attr'=>[
                        'placeholder'=>'npr. 15'
                    ]
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'colors'=>null
        ]);
    }
}
