<?php

namespace App\Form\Admin;

use App\Entity\Profile;
use App\Entity\User;
use App\Service\Utils\ArrayUtils;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('birthday', DateType::class)
            ->add('group', ChoiceType::class, [
                'placeholder' => 'Select group',
                'choices' => ArrayUtils::combineSelf(Profile::getGroups()),
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('region', TextType::class)
            ->add('club', TextType::class)
            ->add('stravaId', TextType::class)
            ->add('user', EntityType::class, [
                'placeholder' => 'Link a user',
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.email', 'ASC');
                },
                'choice_label' => 'email',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
