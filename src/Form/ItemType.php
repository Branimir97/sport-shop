<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Item;
use App\Entity\Size;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                    'placeholder'=>'npr. nogomet'
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
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            })
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
            ->add('image', FileType::class, [
                'mapped'=>false,
//                'multiple'=>true,
                'help'=>'Dopušteni formati slika su: jpg, jpeg i png; Maksimalna dopuštena veličina pojedine slike je 2MB',
                'label'=>'Slike'
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
