<?php

namespace App\Form;

use App\Entity\MultinodeRequestUpdateItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultinodeRequestUpdateItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('chain')
            ->add('hasNewBlock')
            ->add('txUpdateResults', CollectionType::class, [
                'entry_type' => MultinodeRequestUpdateItemResultsType::class,
                'allow_add' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MultinodeRequestUpdateItem::class,
            'csrf_protection' => false,
        ]);
    }
}
