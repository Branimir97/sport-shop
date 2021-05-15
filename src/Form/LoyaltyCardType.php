<?php

namespace App\Form;

use App\Entity\LoyaltyCard;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoyaltyCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $users = $options['users'];
        $isEdit = $options['isEdit'];
            if(!$isEdit) {
                $builder
                    ->add('user', EntityType::class, [
                        'mapped' => false,
                        'class' => User::class,
                        'query_builder' => function (EntityRepository $entityRepository) use ($users) {
                            if(count($users) == 0) {
                                return $entityRepository->createQueryBuilder('u');
                            }
                            return $entityRepository->createQueryBuilder('u')
                                ->where('u NOT IN (:array)')
                                ->setParameter('array', $users);
                        },
                        'choice_label' => 'name',
                        'help' => "Odaberite korisnika kojem želite stvoriti loyalty karticu",
                        'label' => 'Odabir korisnika'
                    ]);
                }
            $builder
            ->add('credits', NumberType::class, [
                'required'=>false,
                'attr'=>[
                    'min'=>0
                ],
                'label'=>'Broj bodova'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LoyaltyCard::class,
            'isEdit'=>false,
            'users'=>[]
        ]);
    }
}
