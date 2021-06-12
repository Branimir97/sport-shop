<?php

namespace App\Form;

use App\Entity\PromoCode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromoCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label'=>'Promo kod',
                'attr'=>[
                    'placeholder'=>'npr. SPORTSHOP20'
                ]
            ])
            ->add('discountPercentage', IntegerType::class, [
                'label'=>'Postotak popusta',
                'attr'=>[
                    'min'=>1,
                    'max'=>30
                ],
                'help'=>'Postotak u rasponu od [1-30]%'
            ])
            ->add('endDate', DateTimeType::class, [
                'label'=>'Vrijeme isteka koda',
                'widget' => 'single_text'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PromoCode::class,
        ]);
    }
}
