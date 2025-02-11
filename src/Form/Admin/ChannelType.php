<?php

namespace App\Form\Admin;

use App\Entity\Channel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du canal',
                'attr' => [
                    'placeholder' => 'Ex: Facebook Ads, Google Ads, etc.',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description du canal de marketing...',
                    'rows' => 4,
                ],
            ])
            ->add('isDefault', CheckboxType::class, [
                'label' => 'Canal par défaut',
                'help' => 'Si coché, ce canal sera automatiquement créé pour tous les nouveaux utilisateurs',
                'required' => false,
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Canal actif',
                'required' => false,
                'data' => true, // Valeur par défaut à true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Channel::class,
        ]);
    }
}
