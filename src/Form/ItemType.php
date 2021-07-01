<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Item;
use App\Entity\Manufacturer;
use App\Entity\Size;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isEditForm = $options['isEdit'];
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'placeholder' => 'form.title_placeholder'
                ],
                'label' => 'form.title_label',
                'translation_domain' => 'item'
            ])
            ->add('manufacturer', EntityType::class, [
                'required' => false,
                'class' => Manufacturer::class,
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('m');
                },
                'choice_label' => 'name',
                'help' => 'form.manufacturer_help',
                'label' => 'form.manufacturer_label',
                'translation_domain' => 'item'
            ])
        ;
        if(!$isEditForm) {
            $builder
                ->add('category', EntityType::class, [
                    'mapped' => false,
                    'required' => false,
                    'class' => Category::class,
                    'multiple' => true,
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository->createQueryBuilder('c');
                    },
                    'choice_label' => 'name',
                    'help' => 'form.category_help',
                    'label' => 'form.category_label',
                    'translation_domain' => 'item'
                ])
                ->add('tag', TextareaType::class, [
                    'required' => false,
                    'mapped' => false,
                    'attr' => [
                        'placeholder' => 'form.tag_placeholder'
                    ],
                    'help' => 'form.tag_help',
                    'label' => 'form.tag_label',
                    'translation_domain' => 'item'
                ])
                ->add('size', EntityType::class, [
                    'required' => false,
                    'mapped' => false,
                    'class' => Size::class,
                    'multiple' => true,
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository->createQueryBuilder('s');
                    },
                    'choice_label' => 'value',
                    'help' => 'form.size_help',
                    'label' => 'form.size_label',
                    'translation_domain' => 'item'
                ])
                ->add('color', EntityType::class, [
                    'required' => false,
                    'mapped' => false,
                    'class' => Color::class,
                    'multiple' => true,
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository->createQueryBuilder('c');
                    },
                    'choice_label' => 'name',
                    'help' => 'form.color_help',
                    'label' => 'form.color_label',
                    'translation_domain' => 'item'
                ])
                ->add('image', FileType::class, [
                    'required' => false,
                    'mapped' => false,
                    'multiple' => true,
                    'help' => 'form.image_help',
                    'label' => 'form.image_label',
                    'translation_domain' => 'item'
                ])
            ;
        }
        $builder
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 10,
                    'placeholder' => 'form.description_placeholder'
                ],
                'help' => 'form.description_help',
                'label' => 'form.description_label',
                'translation_domain' => 'item'
            ])
            ->add('price', NumberType::class, [
                'attr' => [
                    'placeholder' => 'form.price_placeholder'
                ],
                'help' => 'form.price_help',
                'label' => 'form.price_label',
                'translation_domain' => 'item'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
            'isEdit' => false
        ]);
    }
}
