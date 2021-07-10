<?php

namespace App\Form;

use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isEdit = $options['isEdit'];
        if(!$isEdit) {
            $noActionCategories = $options['noActionCategories'];
            $builder
                ->add('category', EntityType::class, [
                    'mapped' => false,
                    'class' => Category::class,
                    'multiple' => true,
                    'query_builder' => function(EntityRepository $entityRepository) use ($noActionCategories) {
                        if(count($noActionCategories) == 0) {
                            return $entityRepository->createQueryBuilder('c');
                        }
                        return $entityRepository->createQueryBuilder('c')
                            ->where('c NOT IN (:array)')
                            ->setParameter('array', $noActionCategories);
                    },
                    'choice_label' => 'name',
                    'help' => 'form.category_help',
                    'label' => 'form.category_label',
                    'translation_domain' => 'action_category'
                ])
            ;
        }
        $builder
            ->add('title_hr', TextType::class, [
                'mapped' => false,
                'label' => 'form.title_label_hr',
                'attr' => [
                    'placeholder' => 'form.title_placeholder_hr'
                ],
                'translation_domain' => 'action_category'
            ])
            ->add('title_en', TextType::class, [
                'mapped' => false,
                'label' => 'form.title_label_en',
                'attr' => [
                    'placeholder' => 'form.title_placeholder_en'
                ],
                'translation_domain' => 'action_category'
            ])
            ->add('discountPercentage', IntegerType::class, [
                'label'=>'form.discount_label',
                'attr' => [
                    'min' => 1,
                    'max' => 30
                ],
                'help' => 'form.discount_help',
                'translation_domain' => 'action_category'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'isEdit' => false,
            'noActionCategories' => []
        ]);
    }
}
