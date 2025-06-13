<?php

namespace App\Form;

use App\Entity\Expense;
use App\Entity\Category;
use App\Entity\User;
use App\Enum\CategoryType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
class ExpenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['user'];

        $builder
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Ex: Courses, Essence, Restaurant...',
                    'class' => 'form-control'
                ],
                'help' => 'Décrivez brièvement cette dépense'
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Montant',
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.01',
                    'placeholder' => '0.00'
                ],
                'help' => 'Montant en euros',
                'scale' => 2
            ])
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'help' => 'Date de la dépense'
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Sélectionner une catégorie (optionnel)',
                'required' => false,
                'label' => 'Catégorie',
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function($repository) use ($user) {
                    return $repository->createQueryBuilder('c')
                        ->where('c.type = :type')
                        ->andWhere('c.user = :user')
                        ->setParameter('type', CategoryType::EXPENSE)
                        ->setParameter('user', $user)
                        ->orderBy('c.name', 'ASC');
                },
                'help' => 'Classez votre dépense dans une catégorie'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Expense::class,
        ]);
        
        $resolver->setRequired('user');
        $resolver->setAllowedTypes('user', User::class);
    }
} 