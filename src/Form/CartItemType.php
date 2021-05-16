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
                    'choices'=>[
                        ''=>''
                    ],
                    'label'=>'Odabir veličine'
                ]);
        } else {
            $builder
                ->add('size', ChoiceType::class, [
                    'choices' => $sizeChoices,
                    'label' => 'Odabir veličine'
                ]);
            }
        if(count($colorChoices) ==0) {
            $builder
                ->add('color', ChoiceType::class, [
                    'choices'=>[
                        ''=>''
                    ],
                    'label'=>'Odabir boje'
                ]);
        } else {
            $builder
                ->add('color', ChoiceType::class, [
                    'choices'=>$colorChoices,
                    'label'=>'Odabir boje'
                ]);
        }
        $builder
            ->add('quantity', IntegerType::class, [
                'label'=>'Količina',
                'attr'=>[
                    'min'=>1
                ],
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data-class'=>CartItem::class,
            'sizeChoices'=>[],
            'colorChoices'=>[]
        ]);
    }
}
