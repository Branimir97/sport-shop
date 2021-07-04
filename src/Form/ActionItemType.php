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
        $actionItems = $options['actionItems'];
        $isEdit = $options['isEdit'];
        if(!$isEdit) {
            $builder
                ->add('item', EntityType::class, [
                    'mapped' => false,
                    'class' => Item::class,
                    'multiple' => true,
                    'query_builder' => function(EntityRepository $entityRepository) use
                                                ($actionItems) {
                        if(count($actionItems) == 0) {
                            return $entityRepository->createQueryBuilder('i');
                        }
                        return $entityRepository->createQueryBuilder('i')
                            ->where('i NOT IN (:array)')
                            ->setParameter('array', $actionItems);
                    },
                    'choice_label' => 'title',
                    'help' => 'form.item_help',
                    'label' => 'form.item_label',
                    'translation_domain' => 'action_item'
                ])
            ;
        }
        $builder
            ->add('title', TextType::class, [
                'label' => 'form.title_label',
                'attr' => [
                    'placeholder' => 'form.title_placeholder'
                ],
                'translation_domain' => 'action_item'
            ])
            ->add('discountPercentage', IntegerType::class, [
                'label' => 'form.discount_label',
                'attr' => [
                    'min' => 1,
                    'max' => 30
                ],
                'help' => 'form.discount_help',
                'translation_domain' => 'action_item'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'isEdit' => false,
            'actionItems' => [],
        ]);
    }
}
