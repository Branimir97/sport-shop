<?php

namespace App\Form;

use App\Entity\User;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints\ValidCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isEditForm = $options['isEditForm'];
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.labels.name',
                'translation_domain' => 'register'
            ])
            ->add('surname', TextType::class, [
                'label' => 'form.labels.surname',
                'translation_domain' => 'register'
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'form.labels.gender_choices.select' => null,
                    'form.labels.gender_choices.male' => 'Muški',
                    'form.labels.gender_choices.female' => 'Ženski'
                ],
                'help' => 'form.help.gender',
                'label' => 'form.labels.gender',
                'translation_domain' => 'register'
            ])
            ->add('birthDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'help' => 'form.help.birthDate',
                'label' => 'form.labels.birthDate',
                'translation_domain' => 'register'
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.labels.email',
                'translation_domain' => 'register'
            ]);

            if(!$isEditForm) {
                $builder
                    ->add('password', RepeatedType::class, [
                        'type' => PasswordType::class,
                        'mapped' => false,
                        'first_options'  => [
                            'help' => 'form.help.password',
                            'label' => 'form.labels.password',
                        ],
                        'second_options' => [
                            'help' => 'form.help.passwordRepeat',
                            'label' => 'form.labels.passwordRepeat',
                        ],
                        'invalid_message' => 'form.invalid_message.password',
                        'translation_domain' => 'register',
                        'constraints' => [
                            new NotBlank([
                                'message' => 'validators.passwordNotBlank',
                            ]),
                            new Length([
                                'min' => 8,
                                'minMessage' => 'validators.passwordLength',
                                'max' => 4096,
                            ]),
                        ],
                    ])
                    ->add('agreeTerms', CheckboxType::class, [
                        'mapped' => false,
                        'constraints' => [
                            new IsTrue([
                                'message' => 'validators.agreeTerms',
                            ]),
                        ],
                        'label_html' => true,
                        'label' => 'form.labels.agreeTerms',
                        'translation_domain' => 'register'
                    ])
                    ->add('subscribeMe', CheckboxType::class, [
                        'required' => false,
                        'mapped' => false,
                        'label' => 'form.labels.subscribe',
                        'translation_domain' => 'register'
                    ])
//                    ->add('captchaCode', CaptchaType::class, [
//                        'mapped' => false,
//                        'captchaConfig' => 'ExampleCaptchaUserRegistration',
////                        'constraints' => [
////                            new ValidCaptcha([
////                                'message' => 'Invalid captcha, please try again'
////                            ])
////                        ]
//                    ])
                ;
            }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'isEditForm' => false,
        ]);
    }
}
