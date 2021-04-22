<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AmountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sizes = $options['sizes'];
        $colors = $options['colors'];

        if(!is_null($sizes)) {
            $builder
                ->add('field_name')
            ;
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
