<?php

namespace App\Form;

use App\Entity\Color;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewItemColorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $colorValues = $options['color_values'];
        $builder
            ->add('color', EntityType::class, [
                'mapped'=> false,
                'class'=>Color::class,
                'multiple'=>true,
                'query_builder'=> function(EntityRepository $entityRepository) use ($colorValues) {
                    if(count($colorValues) == 0) {
                        return $entityRepository->createQueryBuilder('c');
                    }
                    return $entityRepository->createQueryBuilder('c')
                        ->where('c.value NOT IN (:array)')
                        ->setParameter('array', $colorValues);
                },
                'choice_label' => 'value',
                'help' => "Odaberite jednu ili više boja",
                'label' => 'Ostale boje'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'color_values'=>[]
        ]);
    }
}
