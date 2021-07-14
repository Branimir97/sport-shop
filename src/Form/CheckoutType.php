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
                'mapped' => false,
                'choices' => [
                    'form.delivery_address_choices.empty' => ''
                ],
                'label_html' => true,
                'label' => 'form.delivery_address_choices.label',
                'translation_domain' => 'checkout'
            ]);
        } else {
            $builder
                ->add('deliveryAddress', ChoiceType::class, [
                    'mapped' => false,
                    'choices' => $activeUserAddresses,
                    'label' => 'form.delivery_address_choices.label',
                    'label_html' => true,
                    'translation_domain' => 'checkout'
                ])
            ;
        }
        $builder
            ->add('card', ChoiceType::class, [
                'choices'=>[
                    'form.card_choices.credit' => 1,
                    'form.card_choices.debit' => 2,
                    'form.card_choices.paypal' => 3,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'form.card_choices.label',
                'translation_domain' => 'checkout'
            ])
            ->add('card_name_surname', TextType::class, [
                'label' => 'form.card_name_surname_label',
                'attr' => [
                    'placeholder' => 'form.card_name_surname_placeholder'
                ],
                'help' => 'form.card_name_surname_help',
                'translation_domain' => 'checkout'
            ])
            ->add('card_number', TextType::class, [
                'label' => 'form.card_number_label',
                'attr' => [
                    'placeholder' => '0123 3456 6578 8910',
                ],
                'help' => 'form.card_number_help',
                'translation_domain' => 'checkout'
            ])
            ->add('card_expiration', DateType::class, [
                'widget' => 'single_text',
                'label' => 'form.card_expiration_label',
                'help' => 'form.card_expiration_help',
                'translation_domain' => 'checkout'
            ])
            ->add('card_cvv', IntegerType::class, [
                'label' => 'CVV',
                'attr' => [
                    'min' => 0,
                    'placeholder' => 123
                ],
                'help' => 'form.card_cvv_help',
                'translation_domain' => 'checkout'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'activeUserAddresses' => []
        ]);
    }
}
