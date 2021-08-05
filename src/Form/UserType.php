<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isEditForm = $options['isEditForm'];
        $prominentCategories = $options['prominent_categories'];
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
        ;

        if(!$isEditForm) {
            $builder
                ->add('email', EmailType::class, [
                    'label' => 'form.labels.email',
                    'translation_domain' => 'register'
                ])
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
                    'invalid_message' => 'invalid_messages.password',
                    'translation_domain' => 'register',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'constraints.passwordNotBlank',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'constraints.passwordLength',
                            'max' => 4096,
                        ]),
                    ],
                ])
                ->add('agreeTerms', CheckboxType::class, [
                    'mapped' => false,
                    'constraints' => [
                        new IsTrue([
                            'message' => 'constraints.agreeTerms',
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
                ->add('captchaCode',  CaptchaType::class, [
                    'width' => 200,
                    'height' => 50,
                    'length' => 6,
                    'label' => 'Captcha',
                    'invalid_message' => 'invalid_messages.captcha',
                    'background_color' =>  [255, 255, 255],
                    'attr' => [
                        'placeholder' => 'form.captcha'
                    ],
                    'translation_domain' => 'register',
                ])
                ->add('userProminentCategory', EntityType::class, [
                    'required' => false,
                    'mapped' => false,
                    'multiple' => true,
                    'class' => Category::class,
                    'query_builder' => function (EntityRepository $entityRepository)  {
                        return $entityRepository->createQueryBuilder('c');

                    },
                    'choice_label' => 'name',
                    'help' => 'form.help.category',
                    'label' => 'form.labels.category',
                    'translation_domain' => 'register',
                    'constraints' => [
                        new Count([
                            'max' => 10,
                        ])
                    ]
                ])
            ;
        }
        $builder
            ->add('userProminentCategory', EntityType::class, [
            'required' => false,
            'mapped' => false,
            'multiple' => true,
            'class' => Category::class,
            'query_builder' => function (EntityRepository $entityRepository) use ($prominentCategories) {
                if(count($prominentCategories) == 0) {
                    return $entityRepository->createQueryBuilder('c');
                }
                return $entityRepository->createQueryBuilder('c')
                    ->where('c NOT IN (:array)')
                    ->setParameter('array', $prominentCategories);
            },
            'choice_label' => 'name',
            'help' => 'form.help.category',
            'label' => 'form.labels.category',
            'translation_domain' => 'register',
            'constraints' => [
                new Count([
                    'max' => 10 - count($prominentCategories),
                ])
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'isEditForm' => false,
            'prominent_categories' => []
        ]);
    }
}
