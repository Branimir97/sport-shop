<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromoCodeUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $loyaltyCardCredits = $options['loyaltyCardCredits'];
        if(!is_null($loyaltyCardCredits) && $loyaltyCardCredits>0){
            $builder
                ->add('code', TextType::class, [
                    'required' => false,
                    'label' => 'form.code_label',
                    'translation_domain' => 'promo_code_user'
                ])
                ->add('use_credits', CheckboxType::class, [
                    'required' => false,
                    'mapped' => false,
                    'label' => 'form.use_credits_label',
                    'translation_domain' => 'promo_code_user'
                ])
            ;
        } else {
            $builder
                ->add('code', TextType::class, [
                    'label' => 'form.code_label',
                    'translation_domain' => 'promo_code_user'
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'loyaltyCardCredits' => null
        ]);
    }
}
