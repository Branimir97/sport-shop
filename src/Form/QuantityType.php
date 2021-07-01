<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $itemSizes = $options['itemSizes'];
        $itemColors = $options['itemColors'];
        $counter = 0;

        if(!is_null($itemSizes)) {
            foreach($itemSizes as $itemSize) {
                $counter++;
                $builder
                    ->add('itemSize_'.$itemSize->getId(), null, [
                        'attr' => [
                            'placeholder' => 'form_set_size_quantity.size_placeholder',
                            'min' => 0
                        ],
                        'label' => 'form_set_size_quantity.size_label',
                        'label_translation_parameters' => [
                            '%size_value%' => $itemSize->getSize()->getValue()
                        ],
                        'translation_domain' => 'item'
                    ])
                ;
            }
        }
        $counter = 0;
        if(!is_null($itemColors)) {
            foreach($itemColors as $itemColor) {
                $counter++;
                $builder
                    ->add('itemColor_'.$itemColor->getId(), null, [
                        'attr' => [
                            'placeholder' => 'form_set_color_quantity.color_placeholder',
                            'min' => 0
                        ],
                        'label' => 'form_set_color_quantity.color_label',
                        'label_translation_parameters' => [
                            '%color_name%' => $itemColor->getColor()->getName()
                        ],
                        'translation_domain' => 'item'
                    ])
                ;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'itemSizes' => null,
            'itemColors' => null
        ]);
    }
}
