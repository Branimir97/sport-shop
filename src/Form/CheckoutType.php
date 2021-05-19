<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $activeUserAddresses = $options['activeUserAddresses'];
        if(count($activeUserAddresses) == 0){
            $builder
            ->add('deliveryAddress', ChoiceType::class, [
                'mapped'=> false,
                'choices'=>[
                    'Ne postoji ni jedna registrirana adresa isporuke za Vaš račun'=>''
                ],
                'label_html'=>true,
                'label'=>'Odaberite željenu adresu isporuke',
            ]);
        } else {
            $builder
                ->add('deliveryAddress', ChoiceType::class, [
                    'mapped'=> false,
                    'choices'=>$activeUserAddresses,
                    'label'=>'Odaberite željenu adresu isporuke',
                    'label_html'=>true,
                ])
            ;
        }
        $builder
            ->add('card', ChoiceType::class, [
                'choices'=>[
                    'Kreditna kartica'=>'Kreditna kartica',
                    'Debitna kartica'=>'Debitna kartica',
                    'PayPal'=>'PayPal',
                ],
                'expanded'=>true,
                'multiple'=>false,
                'label'=>'Odabir kartice'
            ])
            ->add('card_name', TextType::class, [
                'label'=>'Ime i prezime na kartici',
                'attr'=>[
                    'placeholder'=>'Ime Prezime'
                ],
                'help'=>'Unesite Vaše puno ime kao što je napisano na kartici'
            ])
            ->add('card_number', TextType::class, [
                'label'=>'Broj kartice',
                'attr'=>[
                    'placeholder'=>'0123 3456 6578 8910',
                ],
                'help'=>'Pažljivo prepišite broj s kartice | Nije preporučeno kopiranje'
            ])
            ->add('card_expiration', DateType::class, [
                'widget' => 'single_text',
                'label'=>'Datum isteka kartice',
                'help'=>'Bitni su isključivo mjesec i godina'
            ])
            ->add('card_cvv', IntegerType::class, [
                'label'=>'CVV',
                'attr'=>[
                    'min'=>0,
                    'placeholder'=>123
                ],
                'help'=>'Broj od najčešće 3 znamenke na poleđini Vaše kartice'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'activeUserAddresses'=>[]
        ]);
    }
}
