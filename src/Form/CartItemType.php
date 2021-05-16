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
        $builder
            ->add('size', ChoiceType::class, [
                'choices'=>$sizeChoices,
                'label'=>'Odabir veličine'
            ])
            ->add('color', ChoiceType::class, [
                'choices'=>$colorChoices,
                'label'=>'Odabir boje'
            ])
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
