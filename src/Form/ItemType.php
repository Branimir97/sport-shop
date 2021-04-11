<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Item;
use App\Entity\Size;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'attr'=>[
                    'placeholder'=>'npr. Nike Air Max tenisice'
                ],
                'label'=>'Naziv',
            ])
            ->add('category', EntityType::class, [
                'mapped'=>false,
                'class'=>Category::class,
                'multiple'=>true,
                'query_builder'=>function(EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('c');
                },
                'choice_label'=>'name',
                'help'=>"Odaberite jednu ili više kategorija",
                'label'=>'Kategorije'
            ])
            ->add('tag', TextareaType::class, [
                'mapped'=>false,
                'help'=>"Unesite svaki #tag u novi red",
                'attr'=>[
                    'placeholder'=>'npr. Nogomet'
                ],
                'label'=>'Tagovi',
            ])
            ->add('size', EntityType::class, [
                'mapped'=>false,
                'class'=>Size::class,
                'multiple'=>true,
                'query_builder'=>function(EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('s');
                },
                'choice_label'=>'value',
                'help'=>"Odaberite jednu ili više veličina",
                'label'=>'Dostupne veličine',
            ])
            ->add('color', EntityType::class, [
                'mapped'=>false,
                'class'=>Color::class,
                'multiple'=>true,
                'query_builder'=>function(EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('c');
                },
                'choice_label'=>'value',
                'help'=>"Odaberite jednu ili više boja",
                'label'=>'Dostupne boje',
            ])
            ->add('price', null, [
                'attr'=>[
                    'placeholder'=>'npr. 349,50'
                ],
                'label'=>'Cijena [kn]',
            ])
            ->add('submit', SubmitType::class, [
                'label'=>'Save'
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }
}
