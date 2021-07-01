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
                'label' => 'form_set_color_quantity.edit_quantity_label',
                'attr' => [
                    'min' => 0
                ],
                'translation_domain' => 'item'
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
