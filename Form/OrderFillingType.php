<?php

namespace App\Form;

use App\Entity\Model\OrderFilling;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFillingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('taker', OrderFillingOperationType::class)
            ->add('makers', CollectionType::class, [
                'entry_type' => OrderFillingOperationType::class,
                'allow_add' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderFilling::class,
            'csrf_protection' => false,
        ]);
    }
}
