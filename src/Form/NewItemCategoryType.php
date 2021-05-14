<?php

namespace App\Form;

use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewItemCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categoryNames = $options['category_names'];
        $builder
            ->add('category', EntityType::class, [
                'mapped'=> false,
                'class'=>Category::class,
                'multiple'=>true,
                'query_builder'=> function(EntityRepository $entityRepository) use ($categoryNames) {
                    if(count($categoryNames) == 0) {
                        return $entityRepository->createQueryBuilder('c');
                    }
                    return $entityRepository->createQueryBuilder('c')
                        ->where('c.name NOT IN (:array)')
                        ->setParameter('array', $categoryNames);
                },
                'choice_label' => 'name',
                'help' => "Odaberite jednu ili više kategorija",
                'label' => 'Ostale kategorije'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'category_names'=>[]
        ]);
    }
}
