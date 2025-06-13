<?php

namespace App\Form;

use App\Entity\Category;
use App\Enum\CategoryType as CategoryTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la catégorie',
                'attr' => [
                    'placeholder' => 'Ex: Alimentation, Transport, Salaire...',
                    'class' => 'form-control'
                ],
                'help' => 'Donnez un nom descriptif à votre catégorie'
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de catégorie',
                'choices' => [
                    'Revenu' => CategoryTypeEnum::INCOME,
                    'Dépense' => CategoryTypeEnum::EXPENSE,
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Choisissez si cette catégorie est pour des revenus ou des dépenses'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
} 