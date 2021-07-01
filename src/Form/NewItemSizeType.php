<?php

namespace App\Form;

use App\Entity\Size;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewItemSizeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sizeValues = $options['size_values'];
        $builder
            ->add('size', EntityType::class, [
                'mapped' => false,
                'class' => Size::class,
                'multiple' => true,
                'query_builder' => function(EntityRepository $entityRepository) use ($sizeValues) {
                    if(count($sizeValues) == 0) {
                        return $entityRepository->createQueryBuilder('s');
                    }
                    return $entityRepository->createQueryBuilder('s')
                        ->where('s.value NOT IN (:array)')
                        ->setParameter('array', $sizeValues);
                },
                'choice_label' => 'value',
                'help' => 'form.size_help',
                'label' => 'form_add_size.other_sizes',
                'translation_domain' => 'item'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'size_values' => []
        ]);
    }
}
