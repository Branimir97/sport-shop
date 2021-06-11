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
                        'label'=>'Veličina ['.$itemSize->getSize()->getValue().'] - unesite količinu',
                        'attr'=>[
                            'placeholder'=>'npr. 15',
                            'min'=>0
                        ]
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
                        'label'=>'['.$itemColor->getColor()->getName().'] boja - unesite količinu',
                        'attr'=>[
                            'placeholder'=>'npr. 15',
                            'min'=>0
                        ]
                    ])
                ;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'itemSizes'=>null,
            'itemColors'=>null
        ]);
    }
}
