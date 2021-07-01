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
                    'attr' => [
                        'placeholder' => 'form_set_size_quantity.size_placeholder'
                    ],
                    'label' => 'form_set_size_quantity.size_label',
                    'label_translation_parameters' => [
                        '%size_value%' => $size->getValue()
                    ],
                    'translation_domain' => 'item'
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'sizes' => null
        ]);
    }
}
