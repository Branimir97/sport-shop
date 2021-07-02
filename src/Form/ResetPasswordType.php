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
                    'type' => PasswordType::class,
                    'mapped' => false,
                    'first_options'  => [
                        'label' => 'form_reset_password.password_first_label',
                        'help' => 'form_reset_password.password_first_help',
                    ],
                    'second_options' => [
                        'label' => 'form_reset_password.password_second_label',
                        'help' => 'form_reset_password.password_second_help',
                    ],
                    'invalid_message' => 'form_reset_password.invalid_message',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'form_reset_password.constraint_not_blank'
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'form_reset_password.constraint_length',
                            'max' => 4096
                        ])
                    ],
                    'translation_domain' => 'user'
                ])
            ;
        } else {
            $builder
                ->add('current_password', PasswordType::class, [
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'form_reset_password.current_password_constraint_not_blank'
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'form_reset_password.current_password_constraint_length',
                            'max' => 4096
                        ])
                    ],
                    'label' => 'form_reset_password.current_password_label',
                    'translation_domain' => 'user'
                ])
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'mapped' => false,
                    'first_options'  => [
                        'label' => 'form_reset_password.password_first_label',
                        'help' => 'form_reset_password.password_first_help',
                    ],
                    'second_options' => [
                        'label' => 'form_reset_password.password_second_label',
                        'help' => 'form_reset_password.password_second_help',
                    ],
                    'invalid_message' => 'form_reset_password.invalid_message',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'form_reset_password.constraint_not_blank'
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'form_reset_password.constraint_length',
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
            'isAdmin' => false
        ]);
    }
}
