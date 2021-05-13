<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SizeQuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sizes = $options['sizes'];
        $counter = 0;
        foreach($sizes as $size) {
            $counter++;
            $builder
                ->add('size_'.$counter, null, [
                    'label'=>'['.$size->getValue().'] veličina - unesite količinu',
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
            'sizes'=>null
        ]);
    }
}
