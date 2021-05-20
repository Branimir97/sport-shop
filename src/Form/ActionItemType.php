<?php

namespace App\Form;

use App\Entity\Item;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isEdit = $options['isEdit'];
        if(!$isEdit) {
            $noActionItems = $options['noActionItems'];
            $builder
                ->add('item', EntityType::class, [
                    'mapped'=> false,
                    'class'=>Item::class,
                    'multiple'=>true,
                    'query_builder'=> function(EntityRepository $entityRepository) use ($noActionItems) {
                        if(count($noActionItems) == 0) {
                            return $entityRepository->createQueryBuilder('i');
                        }
                        return $entityRepository->createQueryBuilder('i')
                            ->where('i NOT IN (:array)')
                            ->setParameter('array', $noActionItems);
                    },
                    'choice_label' => 'title',
                    'help' => "Odaberite jedan ili više artikala",
                    'label' => 'Dostupni artikli'
                ])
            ;
        }
        $builder
            ->add('title', TextType::class, [
                'label'=>'Naziv akcije',
                'attr'=>[
                    'placeholder'=>'npr. Popust na artikl - Nike Air Max'
                ]
            ])
            ->add('discountPercentage', IntegerType::class, [
                'label'=>'Postotak popusta',
                'attr'=>[
                    'min'=>1,
                    'max'=>30
                ],
                'help'=>'Postotak u rasponu od [1-30]%'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'isEdit'=>false,
            'noActionItems'=>[]
        ]);
    }
}
