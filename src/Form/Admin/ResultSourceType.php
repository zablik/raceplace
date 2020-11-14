<?php

namespace App\Form\Admin;

use App\Entity\RaceResultsSource;
use App\Service\Utils\ArrayUtils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class ResultSourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'placeholder' => 'Select source type',
                'choices' => ArrayUtils::combineSelf(RaceResultsSource::getTypes()),
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('link', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Url(),
                ]
            ])
            ->add('tableConfigType', ChoiceType::class, [
                'placeholder' => 'Select table config type',
                'choices' => ArrayUtils::combineSelf(RaceResultsSource::getConfigTypes()),
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('checkpointsLink', TextType::class, [
                'constraints' => [
                    new Url(),
                ]
            ])
            ->add('codes', TextType::class)
        ;

        $builder->get('codes')
            ->addModelTransformer(new CallbackTransformer(
                function ($tagsAsArray) {

                    $tagsAsArray = $tagsAsArray ?: [];
                    // transform the array to a string
                    return implode(', ', $tagsAsArray);
                },
                function ($tagsAsString) {
                    // transform the string back to an array
                    return explode(', ', $tagsAsString);
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RaceResultsSource::class,
        ]);
    }
}
