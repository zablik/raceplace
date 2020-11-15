<?php

namespace App\Form\Admin;

use App\Entity\Event;
use App\Entity\Race;
use App\Service\Utils\ArrayUtils;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('type', ChoiceType::class, [
                'placeholder' => 'Select type',
                'choices' => ArrayUtils::combineSelf(Race::getTypes()),
                'preferred_choices' => [
                    Race::TYPE__XCM,
                    Race::TYPE__TRAIL,
                    Race::TYPE__MARATHON,
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('distance', NumberType::class, [
                'scale' => 2,
            ])
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choices' => [$options['event']],
                'choice_label' => 'name',
                'attr' => [
                    'readonly' => true,
                ]
            ])
            ->add('slug', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 50]),
                    new Regex('/^[a-z-0-9]+$/'),
                ]
            ])
            ->add('resultsSource', ResultSourceType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Race::class,
        ]);

        $resolver->setRequired(['event']);
    }
}
