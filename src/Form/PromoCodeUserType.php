<?php

namespace App\Form;

use App\Entity\PromoCodeUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromoCodeUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $loyaltyCardCredits = $options['loyaltyCardCredits'];
        $builder
            ->add('code', TextType::class, [
                'label'=>'Ovdje unesite promo kod'
            ])
        ;
        if(!is_null($loyaltyCardCredits) && $loyaltyCardCredits>0){
            $builder
                ->add('use_credits', CheckboxType::class, [
                    'required'=>false,
                    'mapped'=>false,
                    'label'=>'Želim iskoristiti bodove na kartici ('.$loyaltyCardCredits.')'
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'loyaltyCardCredits'=>null
        ]);
    }
}
