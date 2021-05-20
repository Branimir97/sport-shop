<?php

namespace App\Form;

use App\Entity\ActionCategory;
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
                    'mapped'=> false,
                    'class'=>Category::class,
                    'multiple'=>true,
                    'query_builder'=> function(EntityRepository $entityRepository) use ($noActionCategories) {
                        if(count($noActionCategories) == 0) {
                            return $entityRepository->createQueryBuilder('c');
                        }
                        return $entityRepository->createQueryBuilder('c')
                            ->where('c NOT IN (:array)')
                            ->setParameter('array', $noActionCategories);
                    },
                    'choice_label' => 'name',
                    'help' => "Odaberite jednu ili više kategorija",
                    'label' => 'Dostupne kategorije'
                ])
            ;
        }
        $builder
            ->add('title', TextType::class, [
                'label'=>'Naziv akcije',
                'attr'=>[
                    'placeholder'=>'npr. Popust na sve artikle iz kategorije Muškarci'
                ]
            ])
            ->add('discountPercentage', IntegerType::class, [
                'label'=>'Postotak popusta',
                'attr'=>[
                    'min'=>1,
                    'max'=>30
                ],
                'help'=>'Postotak u rasponu od [1-30]%'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'isEdit'=>false,
            'noActionCategories'=>[]
        ]);
    }
}
