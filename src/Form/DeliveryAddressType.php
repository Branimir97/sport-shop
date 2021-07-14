<?php

namespace App\Form;

use App\Entity\DeliveryAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', null, [
                'attr' => [
                    'placeholder' => 'form.street_placeholder'
                ],
                'label' => 'form.street_label',
                'translation_domain' => 'delivery_address'
            ])
            ->add('city', null, [
                'attr' => [
                    'placeholder' => 'form.city_placeholder'
                ],
                'label' => 'form.city_label',
                'translation_domain' => 'delivery_address'
            ])
            ->add('county', null, [
                'attr' => [
                    'placeholder' => 'form.county_placeholder'
                ],
                'label' => 'form.county_label',
                'translation_domain' => 'delivery_address'
            ])
            ->add('postalCode', null, [
                'attr' => [
                    'placeholder'=>'form.postalCode_placeholder'
                ],
                'label' => 'form.postalCode_label',
                'translation_domain' => 'delivery_address'
            ])
            ->add('country', null, [
                'attr' => [
                    'placeholder' => 'form.country_placeholder'
                ],
                'label' => 'form.country_label',
                'translation_domain' => 'delivery_address'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DeliveryAddress::class
        ]);
    }
}
