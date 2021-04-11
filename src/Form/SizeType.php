<?php

namespace App\Form;

use App\Entity\Size;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SizeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', null, [
                'attr'=>[
                    'placeholder'=>'npr. XXL za odjeću ili 45 za obuću'
                ],
                'label'=>'Veličina'
            ])
            ->add('type', ChoiceType::class, [
                'choices'=>[
                    'Odaberi...'=>null,
                    'Odjeća'=>'Odjeća',
                    'Obuća'=>'Obuća'
                ],
                'help'=>'Odabrati kojoj kategoriji artikla veličina odgovara',
                'label'=>'Kategorija artikla'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Size::class,
        ]);
    }
}
