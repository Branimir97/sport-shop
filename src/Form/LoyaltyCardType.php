<?php

namespace App\Form;

use App\Entity\LoyaltyCard;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoyaltyCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $usersWithLoyaltyCard = $options['usersWithLoyaltyCard'];
        $isEdit = $options['isEdit'];
            if(!$isEdit) {
                $builder
                    ->add('user', EntityType::class, [
                        'mapped' => false,
                        'class' => User::class,
                        'query_builder' => function (EntityRepository $entityRepository)
                                            use ($usersWithLoyaltyCard) {
                            if(count($usersWithLoyaltyCard) == 0) {
                                return $entityRepository->createQueryBuilder('u');
                            }
                            return $entityRepository->createQueryBuilder('u')
                                ->where('u NOT IN (:array)')
                                ->setParameter('array', $usersWithLoyaltyCard);
                        },
                        'choice_label' => function(User $user) {
                                return $user->getFullName();
                        },
                        'help' => 'form.user_help',
                        'label' => 'form.user_label',
                        'translation_domain' => 'loyalty_card'
                    ]);
                }
            $builder
            ->add('credits', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'min' => 0
                ],
                'label' => 'form.credits_label',
                'translation_domain' => 'loyalty_card'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LoyaltyCard::class,
            'isEdit' => false,
            'usersWithLoyaltyCard' => []
        ]);
    }
}
