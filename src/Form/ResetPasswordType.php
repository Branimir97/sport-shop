<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResetPasswordType extends AbstractType
{
    public $translator;

    /**
     * ResetPasswordType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
                    'invalid_message' => $this->translator->trans
                                        ('form_reset_password.invalid_message', [], 'user'),
                    'constraints' => [
                        new NotBlank([
                            'message' => $this->translator->trans
                                        ('form_reset_password.constraint_not_blank', [], 'user'),
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => $this->translator->trans
                                        ('form_reset_password.constraint_length', [], 'user'),
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
                            'message' => $this->translator->trans
                                        ('form_reset_password.current_password_constraint_not_blank',
                                            [], 'user'),
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => $this->translator->trans
                                            ('form_reset_password.current_password_constraint_length',
                                                [], 'user'),
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
                    'invalid_message' => $this->translator->trans
                                        ('form_reset_password.invalid_message', [], 'user'),
                    'constraints' => [
                        new NotBlank([
                            'message' => $this->translator->trans
                                        ('form_reset_password.constraint_not_blank', [], 'user'),
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => $this->translator->trans
                                        ('form_reset_password.constraint_length', [], 'user'),
                            'max' => 4096
                        ])
                    ],
                    'translation_domain' => 'user'
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
