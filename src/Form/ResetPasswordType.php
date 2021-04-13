<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isAdmin = $options['isAdmin'];
        if($isAdmin) {
            $builder
                ->add('password', RepeatedType::class, [
                    'type'=>PasswordType::class,
                    'mapped' => false,
                    'first_options'  => [
                        'label' => 'Nova lozinka *',
                        'help'=>'Lozinka mora sadržavati min. 8 znakova'
                    ],
                    'second_options' => [
                        'label' => 'Ponovni unos nove lozinke *',
                        'help'=>'Lozinke se moraju podudarati'
                    ],
                    'invalid_message'=>'Lozinke se moraju podudarati.',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Unesite novu lozinku'
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Nova lozinka mora sadržavati min. 8 znakova',
                            'max' => 4096
                        ])
                    ]
                ])
            ;
        } else {
            $builder
                ->add('current_password', PasswordType::class, [
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Unesite lozinku'
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Lozinka mora sadržavati min. 8 znakova',
                            'max' => 4096
                        ])
                    ],
                    'label'=>'Trenutna lozinka *'
                ])
                ->add('password', RepeatedType::class, [
                    'type'=>PasswordType::class,
                    'mapped' => false,
                    'first_options'  => [
                        'label' => 'Nova lozinka *',
                        'help'=>'Lozinka mora sadržavati min. 8 znakova'
                    ],
                    'second_options' => [
                        'label' => 'Ponovni unos nove lozinke *',
                        'help'=>'Lozinke se moraju podudarati'
                    ],
                    'invalid_message'=>'Lozinke se moraju podudarati.',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Unesite novu lozinku'
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Nova lozinka mora sadržavati min. 8 znakova',
                            'max' => 4096
                        ])
                    ]
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'isAdmin'=> false
        ]);
    }
}
