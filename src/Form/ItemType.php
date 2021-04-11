<?php

namespace App\Form;

use App\Entity\Item;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('category', TextType::class, [
                'mapped'=>false
            ])
            ->add('tag', TextType::class, [
            'mapped'=>false
            ])
            ->add('size', EntityType::class, [
                'class'=>Size::class,
                'multiple'=>true,
                'query_builder'=>function(EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('s')

                    ;
                }
            ])
            ->add('color', TextType::class, [
                'class'=>Color::class,
                'multiple'=>true,
                'query_builder'=>function(EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('c')
                    ;
                }
            ])
            ->add('price')

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }
}
