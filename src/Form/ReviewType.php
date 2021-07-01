<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rating', RangeType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 5
                ],
                'help' => 'form.rating_help',
                'label' => 'form.rating_label',
                'translation_domain' => 'review'
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'form.comment_label',
                'translation_domain' => 'review'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
