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
                'attr' => [
                    'placeholder' => 'form.code_placeholder'
                ],
                'label' => 'form.code_label',
                'translation_domain' => 'promo_code'
            ])
            ->add('discountPercentage', IntegerType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 30
                ],
                'help' => 'form.discount_percentage_help',
                'label' => 'form.discount_percentage_label',
                'translation_domain' => 'promo_code'
            ])
            ->add('endDate', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'form.end_date_label',
                'translation_domain' => 'promo_code'
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
