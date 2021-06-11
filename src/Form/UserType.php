<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
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
            ->add('name', null, [
                'label'=>'Ime *'
            ])
            ->add('surname', null, [
                'label'=>'Prezime *'
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Odaberi...'=>null,
                    'Muški' => 'Muški',
                    'Ženski' => 'Ženski'
                ],
                'help'=>'Parametar nije obavezan, ali doprinosi boljem korisničkom iskustvu',
                'label'=>'Spol'
            ])
            ->add('birthDate', DateType::class, [
                'required'=>false,
                'widget' => 'single_text',
                'help'=>'Parametar nije obavezan, ali doprinosi boljem korisničkom iskustvu',
                'label'=>'Datum i godina rođenja'
            ])
            ->add('email', null, [
                'label'=>'Email adresa *'
            ]);

            if(!$isEditForm) {
                $builder
                    ->add('password', RepeatedType::class, [
                        'type'=>PasswordType::class,
                        'mapped' => false,
                        'first_options'  => [
                            'label' => 'Lozinka *',
                            'help'=>'Lozinka mora sadržavati min. 8 znakova'
                        ],
                        'second_options' => [
                            'label' => 'Ponovni unos lozinke *',
                            'help'=>'Lozinke se moraju podudarati'
                        ],
                        'invalid_message' => 'Lozinke se moraju podudarati.',
                        'constraints' => [
                            new NotBlank([
                                'message' => 'Unesite lozinku',
                            ]),
                            new Length([
                                'min' => 8,
                                'minMessage' => 'Lozinka mora sadržavati min. 8 znakova',
                                // max length allowed by Symfony for security reasons
                                'max' => 4096,
                            ]),
                        ],
                    ])
                    ->add('agreeTerms', CheckboxType::class, [
                        'mapped' => false,
                        'constraints' => [
                            new IsTrue([
                                'message' => 'Za uspješnu registraciju potrebno je prihvatiti 
                                naše uvjete i odredbe našeg web mjesta.',
                            ]),
                        ],
                        'label_html'=>true,
                        'label'=>'Registracijom prihvaćate uvjete i odredbe našeg web shopa.'
                    ])
                    ->add('subscribeMe', CheckboxType::class, [
                        'required'=>false,
                        'mapped' => false,
                        'label'=>'Želim primati sve novosti o SportShop akcijama, promo kodovima i ostalim važnim događajima.'
                    ])
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
