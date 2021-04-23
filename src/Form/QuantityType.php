<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sizes = $options['sizes'];
        $colors = $options['colors'];


        if(!is_null($sizes)) {
            foreach($sizes as $size) {
                $builder
                    ->add('size_'.$size->getValue(), null, [
                        'label'=>'Veličina ['.$size->getValue().'] - unesite količinu',
                        'attr'=>[
                            'placeholder'=>'npr. 15'
                        ]
                    ])
                ;
            }
        }

        if(!is_null($colors)) {
            foreach($colors as $color) {
                $builder
                    ->add('color_'.$color->getValue(), null, [
                        'label'=>'['.$color->getValue().'] boja - unesite količinu',
                        'attr'=>[
                            'placeholder'=>'npr. 15'
                        ]
                    ])
                ;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'sizes'=>null,
            'colors'=>null
        ]);
    }
}