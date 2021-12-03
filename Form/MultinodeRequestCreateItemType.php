<?php

namespace App\Form;

use App\Entity\MultinodeRequestCreateItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultinodeRequestCreateItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status')
            ->add('txDbId')
            ->add('txId')
            ->add('userId')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MultinodeRequestCreateItem::class,
            'csrf_protection' => false,
        ]);
    }
}
