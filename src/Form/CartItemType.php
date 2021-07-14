<?php

namespace App\Form;

use App\Entity\CartItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sizeChoices = $options['sizeChoices'];
        $colorChoices = $options['colorChoices'];

        if(count($sizeChoices) == 0) {
            $builder
                ->add('size', ChoiceType::class, [
                    'choices' => [
                        '' => ''
                    ],
                    'label' => 'form.size_label',
                    'translation_domain' => 'cart'
                ]);
        } else {
            $builder
                ->add('size', ChoiceType::class, [
                    'choices' => $sizeChoices,
                    'label' => 'form.size_label',
                    'translation_domain' => 'cart'
                ]);
            }
        if(count($colorChoices) ==0) {
            $builder
                ->add('color', ChoiceType::class, [
                    'choices' => [
                        '' => ''
                    ],
                    'label' => 'form.color_label',
                    'translation_domain' => 'cart'
                ]);
        } else {
            $builder
                ->add('color', ChoiceType::class, [
                    'choices' => $colorChoices,
                    'label' => 'form.color_label',
                    'translation_domain' => 'cart'
                ]);
        }
        $builder
            ->add('quantity', IntegerType::class, [
                'label' => 'form.quantity_label',
                'attr' => [
                    'min' => 1
                ],
                'data' => 1,
                'translation_domain' => 'cart'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data-class' => CartItem::class,
            'sizeChoices' => [],
            'colorChoices' => []
        ]);
    }
}
