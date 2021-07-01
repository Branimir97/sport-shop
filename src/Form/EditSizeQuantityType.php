<?php

namespace App\Form;

use App\Entity\ItemSize;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditSizeQuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', null, [
                'label' => 'form_set_size_quantity.edit_quantity_label',
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
            'data_class' => ItemSize::class,
            ]);
    }
}
