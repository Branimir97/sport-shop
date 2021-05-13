<?php

namespace App\Form;

use App\Entity\ItemColor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditColorQuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', null, [
                'label'=>'Unesite novu količinu',
                'attr'=>[
                    'min'=>0
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemColor::class,
        ]);
    }
}
