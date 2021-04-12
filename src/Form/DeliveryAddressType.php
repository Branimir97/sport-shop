<?php

namespace App\Form;

use App\Entity\DeliveryAddress;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', null, [
                'attr'=>[
                    'placeholder'=>'npr. Kneza Trpimira 2B'
                ],
                'label'=>'Ulica'
            ])
            ->add('city', null, [
                'attr'=>[
                    'placeholder'=>'npr. Osijek'
                ],
                'label'=>'Grad'
            ])
            ->add('county', null, [
                'attr'=>[
                    'placeholder'=>'npr. Osječko-Baranjska'
                ],
                'label'=>'Županija'
            ])
            ->add('postalCode', null, [
                'attr'=>[
                    'placeholder'=>'npr. 31000'
                ],
                'label'=>'Poštanski broj'
            ])
            ->add('country', null, [
                'attr'=>[
                    'placeholder'=>'npr. Hrvatska'
                ],
                'label'=>'Država'
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
