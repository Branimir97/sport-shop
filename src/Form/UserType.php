<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('name')
            ->add('surname')
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Odaberi...'=>0,
                    'Muški' => 'Muški',
                    'Ženski' => 'Ženski'
                ]
            ])
            ->add('birthDate', DateType::class, [
                'widget'=>'single_text'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
